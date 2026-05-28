<?php
  declare(strict_types = 1);

  /**
   * Gym-wide analytics for the admin dashboard: popular classes, equipment
   * usage, member retention, membership mix, and dispute load.
   */
  class AdminAnalytics {

    /** Counts of rows in key tables for the admin dashboard. */
    public static function getDashboardCounts(PDO $db) : array {
      // Table names cannot be PDO-parameterised, so the list below is the
      // explicit whitelist of identifiers that may be interpolated.
      $counts = [];
      foreach (['User','Class','Schedule','Enrollment','Equipment','Waitlist','Review'] as $t) {
        $counts[$t] = (int)$db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
      }
      return $counts;
    }

    /** Headline counts for the dashboard cards. */
    public static function getOverview(PDO $db) : array {
      $members      = (int)$db->query("SELECT COUNT(*) FROM User WHERE Role = 'member'")->fetchColumn();
      $activeSubs   = (int)$db->query("SELECT COUNT(*) FROM Membership WHERE Status = 'active'")->fetchColumn();
      $enrollments  = (int)$db->query("SELECT COUNT(*) FROM Enrollment WHERE Status = 'enrolled'")->fetchColumn();
      $bookings     = (int)$db->query("SELECT COUNT(*) FROM Booking WHERE Status = 'booked'")->fetchColumn();
      $openDisputes = (int)$db->query("SELECT COUNT(*) FROM Dispute WHERE Status IN ('open','in_review')")->fetchColumn();

      // Monthly recurring revenue from active subscriptions.
      $mrr = (int)$db->query("
        SELECT COALESCE(SUM(p.Price), 0)
        FROM Membership m JOIN Plan p ON p.PlanId = m.PlanId
        WHERE m.Status = 'active'
      ")->fetchColumn();

      return [
        'members'      => $members,
        'activeSubs'   => $activeSubs,
        'enrollments'  => $enrollments,
        'bookings'     => $bookings,
        'openDisputes' => $openDisputes,
        'mrr'          => $mrr,
      ];
    }

    /** Most popular classes by total enrolment, with average rating. */
    public static function getPopularClasses(PDO $db, int $limit = 6) : array {
      $stmt = $db->prepare("
        SELECT c.ClassId, c.Name, c.Type,
               (SELECT COUNT(*) FROM Enrollment e JOIN Schedule s ON s.ScheduleId = e.ScheduleId
                  WHERE s.ClassId = c.ClassId AND e.Status IN ('enrolled','attended')) AS Enrolled,
               (SELECT AVG(Rating) FROM Review r WHERE r.ClassId = c.ClassId) AS AvgRating
        FROM Class c
        ORDER BY Enrolled DESC, c.Name
        LIMIT ?
      ");
      $stmt->execute(array($limit));
      $rows = array();
      while ($row = $stmt->fetch()) {
        $row['Enrolled']  = (int)$row['Enrolled'];
        $row['AvgRating'] = $row['AvgRating'] !== null ? round((float)$row['AvgRating'], 1) : null;
        $rows[] = $row;
      }
      return $rows;
    }

    /**
     * Equipment usage / availability by location: how much of each location's
     * inventory is currently available vs. in use or out of service.
     */
    public static function getEquipmentUsage(PDO $db) : array {
      $stmt = $db->query("
        SELECT l.Name AS Location,
               COUNT(*) AS Items,
               SUM(eq.Quantity) AS TotalUnits,
               SUM(eq.Available) AS AvailableUnits,
               SUM(CASE WHEN eq.Status != 'operational' THEN 1 ELSE 0 END) AS NonOperational
        FROM Equipment eq JOIN Location l ON l.LocationId = eq.LocationId
        GROUP BY l.LocationId, l.Name
        ORDER BY l.Name
      ");
      $rows = array();
      while ($row = $stmt->fetch()) {
        $total = (int)$row['TotalUnits'];
        $avail = (int)$row['AvailableUnits'];
        $row['Items']          = (int)$row['Items'];
        $row['TotalUnits']     = $total;
        $row['AvailableUnits'] = $avail;
        $row['NonOperational'] = (int)$row['NonOperational'];
        $row['UtilisationPct'] = $total > 0 ? round((($total - $avail) / $total) * 100) : 0;
        $rows[] = $row;
      }
      return $rows;
    }

    /**
     * Member retention proxy: of members who have ever enrolled, how many are
     * still active (have an enrolment or upcoming booking). Returns a percentage
     * plus the raw counts.
     */
    public static function getRetention(PDO $db) : array {
      $totalMembers = (int)$db->query("SELECT COUNT(*) FROM User WHERE Role = 'member'")->fetchColumn();

      $engaged = (int)$db->query("
        SELECT COUNT(DISTINCT UserId) FROM (
          SELECT UserId FROM Enrollment WHERE Status = 'enrolled'
          UNION
          SELECT UserId FROM Booking    WHERE Status = 'booked'
          UNION
          SELECT UserId FROM Membership WHERE Status = 'active'
        )
      ")->fetchColumn();

      return [
        'totalMembers' => $totalMembers,
        'engaged'      => $engaged,
        'retentionPct' => $totalMembers > 0 ? round(($engaged / $totalMembers) * 100) : 0,
      ];
    }

    /** Distribution of active subscriptions across plans. */
    public static function getPlanDistribution(PDO $db) : array {
      $stmt = $db->query("
        SELECT p.Name, p.Tier,
               (SELECT COUNT(*) FROM Membership m WHERE m.PlanId = p.PlanId AND m.Status = 'active') AS Subscribers
        FROM Plan p ORDER BY p.Tier
      ");
      $rows = array();
      while ($row = $stmt->fetch()) {
        $row['Subscribers'] = (int)$row['Subscribers'];
        $rows[] = $row;
      }
      return $rows;
    }
  }
?>
