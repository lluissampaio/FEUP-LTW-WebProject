<?php
  declare(strict_types = 1);

  class TrainerAnalytics {
    
    public static function getOverallStats(PDO $db, int $trainerId): array {
      $stmt = $db->prepare("
        SELECT 
          COUNT(DISTINCT c.ClassId) as TotalClasses,
          COUNT(DISTINCT s.ScheduleId) as TotalSessions,
          (SELECT COUNT(*) FROM Enrollment e JOIN Schedule s2 ON s2.ScheduleId = e.ScheduleId JOIN Class c2 ON c2.ClassId = s2.ClassId WHERE c2.TrainerId = ? AND e.Status IN ('enrolled', 'attended')) as TotalEnrollments,
          (SELECT COUNT(*) FROM Enrollment e JOIN Schedule s2 ON s2.ScheduleId = e.ScheduleId JOIN Class c2 ON c2.ClassId = s2.ClassId WHERE c2.TrainerId = ? AND e.Status = 'attended') as TotalAttended,
          (SELECT AVG(Rating) FROM Review r JOIN Class c2 ON c2.ClassId = r.ClassId WHERE c2.TrainerId = ?) as AverageRating,
          (SELECT COUNT(*) FROM Review r JOIN Class c2 ON c2.ClassId = r.ClassId WHERE c2.TrainerId = ?) as TotalReviews
        FROM Class c 
        LEFT JOIN Schedule s ON s.ClassId = c.ClassId
        WHERE c.TrainerId = ?
      ");
      $stmt->execute([$trainerId, $trainerId, $trainerId, $trainerId, $trainerId]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$row) return [];
      
      $row['TotalClasses'] = (int)$row['TotalClasses'];
      $row['TotalSessions'] = (int)$row['TotalSessions'];
      $row['TotalEnrollments'] = (int)$row['TotalEnrollments'];
      $row['TotalAttended'] = (int)$row['TotalAttended'];
      $row['AverageRating'] = $row['AverageRating'] !== null ? round((float)$row['AverageRating'], 1) : null;
      $row['TotalReviews'] = (int)$row['TotalReviews'];
      
      $row['AttendanceRate'] = $row['TotalEnrollments'] > 0 
        ? round(($row['TotalAttended'] / $row['TotalEnrollments']) * 100) 
        : 0;

      return $row;
    }

    public static function getClassStats(PDO $db, int $trainerId): array {
      $stmt = $db->prepare("
        SELECT 
          c.ClassId, 
          c.Name, 
          c.Type, 
          c.Capacity,
          COUNT(DISTINCT s.ScheduleId) as TotalSessions,
          (SELECT COUNT(*) FROM Enrollment e JOIN Schedule s2 ON s2.ScheduleId = e.ScheduleId WHERE s2.ClassId = c.ClassId AND e.Status IN ('enrolled', 'attended')) as TotalEnrollments,
          (SELECT COUNT(*) FROM Enrollment e JOIN Schedule s2 ON s2.ScheduleId = e.ScheduleId WHERE s2.ClassId = c.ClassId AND e.Status = 'attended') as TotalAttended,
          (SELECT AVG(Rating) FROM Review r WHERE r.ClassId = c.ClassId) as AverageRating
        FROM Class c
        LEFT JOIN Schedule s ON s.ClassId = c.ClassId
        WHERE c.TrainerId = ?
        GROUP BY c.ClassId, c.Name, c.Type, c.Capacity
        ORDER BY c.Name ASC
      ");
      $stmt->execute([$trainerId]);
      $results = [];
      
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['ClassId'] = (int)$row['ClassId'];
        $row['Capacity'] = (int)$row['Capacity'];
        $row['TotalSessions'] = (int)$row['TotalSessions'];
        $row['TotalEnrollments'] = (int)$row['TotalEnrollments'];
        $row['TotalAttended'] = (int)$row['TotalAttended'];
        $row['AverageRating'] = $row['AverageRating'] !== null ? round((float)$row['AverageRating'], 1) : null;
        
        $row['FillRate'] = $row['TotalSessions'] > 0 
          ? round(($row['TotalEnrollments'] / ($row['Capacity'] * $row['TotalSessions'])) * 100)
          : 0;
          
        $results[] = $row;
      }
      
      return $results;
    }
  }
?>
