/*******************************************************************************
   Iron Haven Database
   DB Server: SQLite
   Default password for all seeded accounts: "password"
   (bcrypt hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
********************************************************************************/

DROP TABLE IF EXISTS NutritionLog;
DROP TABLE IF EXISTS NutritionMeal;
DROP TABLE IF EXISTS FitnessGoal;
DROP TABLE IF EXISTS EquipmentReservation;
DROP TABLE IF EXISTS MembershipApplication;
DROP TABLE IF EXISTS NutritionPlan;
DROP TABLE IF EXISTS Dispute;
DROP TABLE IF EXISTS Booking;
DROP TABLE IF EXISTS TrainerAvailability;
DROP TABLE IF EXISTS Membership;
DROP TABLE IF EXISTS Plan;
DROP TABLE IF EXISTS Notification;
DROP TABLE IF EXISTS Waitlist;
DROP TABLE IF EXISTS Review;
DROP TABLE IF EXISTS Enrollment;
DROP TABLE IF EXISTS Workout;
DROP TABLE IF EXISTS Schedule;
DROP TABLE IF EXISTS Equipment;
DROP TABLE IF EXISTS Class;
DROP TABLE IF EXISTS Trainer;
DROP TABLE IF EXISTS Location;
DROP TABLE IF EXISTS User;

/*******************************************************************************
   Create Tables
********************************************************************************/

CREATE TABLE User (
    UserId      INTEGER       NOT NULL,
    FirstName   NVARCHAR(40)  NOT NULL,
    LastName    NVARCHAR(40)  NOT NULL,
    Username    NVARCHAR(20)  NOT NULL,
    Email       NVARCHAR(120) NOT NULL,
    Password    NVARCHAR(255) NOT NULL,
    Photo       NVARCHAR(255),
    Role        NVARCHAR(20)  NOT NULL DEFAULT 'member',  -- 'member' | 'trainer' | 'admin'
    Active      INTEGER       NOT NULL DEFAULT 1,
    JoinDate    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_User PRIMARY KEY (UserId),
    CONSTRAINT UQ_User_Email    UNIQUE (Email),
    CONSTRAINT UQ_User_Username UNIQUE (Username),
    CONSTRAINT CK_User_Role     CHECK (Role IN ('member','trainer','admin'))
);

CREATE TABLE Location (
    LocationId  INTEGER       NOT NULL,
    Name        NVARCHAR(80)  NOT NULL,
    City        NVARCHAR(60)  NOT NULL,
    Country     NVARCHAR(60)  NOT NULL,
    Address     NVARCHAR(200),
    Photo       NVARCHAR(255),
    CONSTRAINT PK_Location PRIMARY KEY (LocationId)
);

CREATE TABLE Trainer (
    TrainerId       INTEGER       NOT NULL,  -- = User.UserId
    Bio             TEXT,
    Specialty       NVARCHAR(120),
    Certifications  TEXT,
    Featured        INTEGER       NOT NULL DEFAULT 0,  -- admin promo: highlight on homepage
    CONSTRAINT PK_Trainer PRIMARY KEY (TrainerId),
    FOREIGN KEY (TrainerId) REFERENCES User (UserId) ON DELETE CASCADE
);

CREATE TABLE Class (
    ClassId      INTEGER        NOT NULL,
    Name         NVARCHAR(120)  NOT NULL,
    Type         NVARCHAR(60)   NOT NULL,  -- e.g. 'Strength','Mobility','Cardio','Recovery','HIIT'
    Description  TEXT,
    TrainerId    INTEGER        NOT NULL,
    LocationId   INTEGER        NOT NULL,
    Capacity     INTEGER        NOT NULL DEFAULT 10,
    Featured     INTEGER        NOT NULL DEFAULT 0,  -- admin promo: highlight on homepage
    CONSTRAINT PK_Class PRIMARY KEY (ClassId),
    FOREIGN KEY (TrainerId)  REFERENCES Trainer  (TrainerId),
    FOREIGN KEY (LocationId) REFERENCES Location (LocationId)
);

CREATE TABLE Schedule (
    ScheduleId  INTEGER       NOT NULL,
    ClassId     INTEGER       NOT NULL,
    DayOfWeek   NVARCHAR(12)  NOT NULL,  -- 'Monday'..'Sunday'
    StartTime   NVARCHAR(5)   NOT NULL,  -- 'HH:MM'
    Duration    INTEGER       NOT NULL,  -- minutes
    CONSTRAINT PK_Schedule PRIMARY KEY (ScheduleId),
    CONSTRAINT CK_Schedule_Day CHECK (DayOfWeek IN ('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')),
    FOREIGN KEY (ClassId) REFERENCES Class (ClassId) ON DELETE CASCADE
);

CREATE TABLE Enrollment (
    EnrollmentId  INTEGER       NOT NULL,
    UserId        INTEGER       NOT NULL,
    ScheduleId    INTEGER       NOT NULL,
    Status        NVARCHAR(20)  NOT NULL DEFAULT 'enrolled',  -- 'enrolled' | 'cancelled' | 'attended'
    EnrolledAt    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_Enrollment PRIMARY KEY (EnrollmentId),
    CONSTRAINT UQ_Enrollment UNIQUE (UserId, ScheduleId),
    CONSTRAINT CK_Enrollment_Status CHECK (Status IN ('enrolled','cancelled','attended')),
    FOREIGN KEY (UserId)     REFERENCES User     (UserId)     ON DELETE CASCADE,
    FOREIGN KEY (ScheduleId) REFERENCES Schedule (ScheduleId) ON DELETE CASCADE
);

CREATE TABLE Equipment (
    EquipmentId  INTEGER        NOT NULL,
    Name         NVARCHAR(120)  NOT NULL,
    Type         NVARCHAR(60)   NOT NULL,  -- e.g. 'Cardio','Strength','Free Weights','Recovery'
    LocationId   INTEGER        NOT NULL,
    Quantity     INTEGER        NOT NULL DEFAULT 1,
    Available    INTEGER        NOT NULL DEFAULT 1,
    Status       NVARCHAR(20)   NOT NULL DEFAULT 'operational',  -- 'operational' | 'maintenance' | 'out_of_order'
    CONSTRAINT PK_Equipment PRIMARY KEY (EquipmentId),
    CONSTRAINT CK_Equipment_Status CHECK (Status IN ('operational','maintenance','out_of_order')),
    FOREIGN KEY (LocationId) REFERENCES Location (LocationId)
);

CREATE TABLE Review (
    ReviewId    INTEGER    NOT NULL,
    UserId      INTEGER    NOT NULL,
    ClassId     INTEGER    NOT NULL,
    Rating      INTEGER    NOT NULL,  -- 1..5
    Comment     TEXT,
    CreatedAt   DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_Review PRIMARY KEY (ReviewId),
    CONSTRAINT UQ_Review UNIQUE (UserId, ClassId),
    CONSTRAINT CK_Review_Rating CHECK (Rating BETWEEN 1 AND 5),
    FOREIGN KEY (UserId)  REFERENCES User  (UserId)  ON DELETE CASCADE,
    FOREIGN KEY (ClassId) REFERENCES Class (ClassId) ON DELETE CASCADE
);

CREATE TABLE Waitlist (
    WaitlistId  INTEGER       NOT NULL,
    UserId      INTEGER       NOT NULL,
    ScheduleId  INTEGER       NOT NULL,
    Status      NVARCHAR(20)  NOT NULL DEFAULT 'waiting',  -- 'waiting' | 'promoted' | 'cancelled'
    JoinedAt    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_Waitlist PRIMARY KEY (WaitlistId),
    CONSTRAINT UQ_Waitlist UNIQUE (UserId, ScheduleId),
    CONSTRAINT CK_Waitlist_Status CHECK (Status IN ('waiting','promoted','cancelled')),
    FOREIGN KEY (UserId)     REFERENCES User     (UserId)     ON DELETE CASCADE,
    FOREIGN KEY (ScheduleId) REFERENCES Schedule (ScheduleId) ON DELETE CASCADE
);

CREATE TABLE Notification (
    NotificationId  INTEGER       NOT NULL,
    UserId          INTEGER       NOT NULL,
    Type            NVARCHAR(40)  NOT NULL,   -- 'enrollment' | 'cancellation' | 'waitlist' | 'promoted' | 'review' | 'system'
    Title           NVARCHAR(120) NOT NULL,
    Message         TEXT          NOT NULL,
    Link            NVARCHAR(255),
    ReadFlag        INTEGER       NOT NULL DEFAULT 0,
    Emailed         INTEGER       NOT NULL DEFAULT 0,  -- 1 once an email copy has been sent
    CreatedAt       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_Notification PRIMARY KEY (NotificationId),
    FOREIGN KEY (UserId) REFERENCES User (UserId) ON DELETE CASCADE
);

CREATE TABLE Workout (
    WorkoutId        INTEGER       NOT NULL,
    UserId           INTEGER       NOT NULL,
    WorkoutType      NVARCHAR(60)  NOT NULL,
    DurationMinutes  INTEGER,
    Calories         INTEGER,
    Date             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Status           NVARCHAR(20)  NOT NULL DEFAULT 'Completed',
    CONSTRAINT PK_Workout PRIMARY KEY (WorkoutId),
    FOREIGN KEY (UserId) REFERENCES User (UserId) ON DELETE CASCADE
);

/*******************************************************************************
   Membership plans & subscriptions (Francisco's slice)
********************************************************************************/

CREATE TABLE Plan (
    PlanId       INTEGER        NOT NULL,
    Name         NVARCHAR(60)   NOT NULL,
    Tier         INTEGER        NOT NULL,             -- ordering for upgrade/downgrade (1 = lowest)
    Price        INTEGER        NOT NULL,             -- price per month, in whole currency units
    Period       NVARCHAR(40)   NOT NULL DEFAULT 'per month',
    Description  TEXT,
    Featured     INTEGER        NOT NULL DEFAULT 0,
    PTSessionsLimit INTEGER     NOT NULL DEFAULT 0,
    CONSTRAINT PK_Plan PRIMARY KEY (PlanId),
    CONSTRAINT UQ_Plan_Name UNIQUE (Name),
    CONSTRAINT UQ_Plan_Tier UNIQUE (Tier)
);

CREATE TABLE Membership (
    MembershipId INTEGER        NOT NULL,
    UserId       INTEGER        NOT NULL,
    PlanId       INTEGER        NOT NULL,
    Status       NVARCHAR(20)   NOT NULL DEFAULT 'active',  -- 'active' | 'cancelled'
    StartedAt    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    RenewsAt     DATETIME,
    CONSTRAINT PK_Membership PRIMARY KEY (MembershipId),
    CONSTRAINT UQ_Membership_User UNIQUE (UserId),         -- one subscription record per member
    CONSTRAINT CK_Membership_Status CHECK (Status IN ('active','cancelled')),
    FOREIGN KEY (UserId) REFERENCES User (UserId) ON DELETE CASCADE,
    FOREIGN KEY (PlanId) REFERENCES Plan (PlanId)
);

/*******************************************************************************
   Personal training: trainer availability + one-on-one bookings
********************************************************************************/

CREATE TABLE TrainerAvailability (
    AvailabilityId INTEGER       NOT NULL,
    TrainerId      INTEGER       NOT NULL,
    DayOfWeek      NVARCHAR(12)  NOT NULL,             -- 'Monday'..'Sunday'
    StartTime      NVARCHAR(5)   NOT NULL,             -- 'HH:MM'
    Duration       INTEGER       NOT NULL DEFAULT 60,  -- minutes
    CONSTRAINT PK_TrainerAvailability PRIMARY KEY (AvailabilityId),
    CONSTRAINT UQ_TrainerAvailability UNIQUE (TrainerId, DayOfWeek, StartTime),
    CONSTRAINT CK_Availability_Day CHECK (DayOfWeek IN ('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')),
    FOREIGN KEY (TrainerId) REFERENCES Trainer (TrainerId) ON DELETE CASCADE
);

CREATE TABLE Booking (
    BookingId      INTEGER       NOT NULL,
    UserId         INTEGER       NOT NULL,             -- the member who booked
    TrainerId      INTEGER       NOT NULL,
    AvailabilityId INTEGER       NOT NULL,
    BookingDate    DATE          NOT NULL,             -- 'YYYY-MM-DD' of the chosen occurrence
    Status         NVARCHAR(20)  NOT NULL DEFAULT 'booked',  -- 'booked' | 'cancelled' | 'completed'
    Notes          TEXT,
    CreatedAt      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_Booking PRIMARY KEY (BookingId),
    CONSTRAINT CK_Booking_Status CHECK (Status IN ('booked','cancelled','completed')),
    FOREIGN KEY (UserId)         REFERENCES User                (UserId)         ON DELETE CASCADE,
    FOREIGN KEY (TrainerId)      REFERENCES Trainer             (TrainerId)      ON DELETE CASCADE,
    FOREIGN KEY (AvailabilityId) REFERENCES TrainerAvailability (AvailabilityId) ON DELETE CASCADE
);

CREATE UNIQUE INDEX UQ_Booking_Active ON Booking (AvailabilityId, BookingDate) WHERE Status = 'booked';

/*******************************************************************************
   Disputes & feedback (member reports, admin responses)
********************************************************************************/

CREATE TABLE Dispute (
    DisputeId    INTEGER        NOT NULL,
    UserId       INTEGER        NOT NULL,             -- member who raised it
    Category     NVARCHAR(40)   NOT NULL,             -- 'equipment' | 'class' | 'billing' | 'staff' | 'other'
    Subject      NVARCHAR(140)  NOT NULL,
    Message      TEXT           NOT NULL,
    Status       NVARCHAR(20)   NOT NULL DEFAULT 'open',  -- 'open' | 'in_review' | 'resolved'
    Response     TEXT,
    RespondedBy  INTEGER,                              -- admin UserId
    CreatedAt    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_Dispute PRIMARY KEY (DisputeId),
    CONSTRAINT CK_Dispute_Category CHECK (Category IN ('equipment','class','billing','staff','other')),
    CONSTRAINT CK_Dispute_Status   CHECK (Status IN ('open','in_review','resolved')),
    FOREIGN KEY (UserId)      REFERENCES User (UserId) ON DELETE CASCADE,
    FOREIGN KEY (RespondedBy) REFERENCES User (UserId)
);

/*******************************************************************************
   Nutrition Plan (member goals and macros)
********************************************************************************/

CREATE TABLE NutritionPlan (
    PlanId       INTEGER       NOT NULL,
    UserId       INTEGER       NOT NULL,
    DietType     NVARCHAR(60)  NOT NULL,
    DailyCalories INTEGER      NOT NULL,
    Protein      INTEGER       NOT NULL,
    Carbs        INTEGER       NOT NULL,
    Fats         INTEGER       NOT NULL,
    Guidelines   TEXT          NOT NULL,
    CreatedAt    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_NutritionPlan PRIMARY KEY (PlanId),
    CONSTRAINT UQ_NutritionPlan_User UNIQUE (UserId),
    FOREIGN KEY (UserId) REFERENCES User (UserId) ON DELETE CASCADE
);

/*******************************************************************************
   Membership applications (apply to join → admin accepts/rejects → email may register)
********************************************************************************/

CREATE TABLE MembershipApplication (
    ApplicationId INTEGER       NOT NULL,
    UserId        INTEGER       NOT NULL,                    -- the member applying
    PlanId        INTEGER       NOT NULL,                    -- the plan they applied for
    Message       TEXT,                                      -- optional note written by the applicant
    Status        NVARCHAR(20)  NOT NULL DEFAULT 'pending',  -- 'pending' | 'accepted' | 'rejected'
    CreatedAt     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DecidedAt     DATETIME,
    DecidedBy     INTEGER,                                   -- admin UserId who decided
    CONSTRAINT PK_MembershipApplication PRIMARY KEY (ApplicationId),
    CONSTRAINT CK_Application_Status CHECK (Status IN ('pending','accepted','rejected')),
    FOREIGN KEY (UserId)    REFERENCES User (UserId) ON DELETE CASCADE,
    FOREIGN KEY (PlanId)    REFERENCES Plan (PlanId) ON DELETE CASCADE,
    FOREIGN KEY (DecidedBy) REFERENCES User (UserId)
);

-- A member may have only one pending application per plan.
CREATE UNIQUE INDEX UQ_Application_Pending ON MembershipApplication (UserId, PlanId) WHERE Status = 'pending';

/*******************************************************************************
   Equipment reservation (member reserves a piece of equipment for a time slot)
********************************************************************************/

CREATE TABLE EquipmentReservation (
    ReservationId   INTEGER       NOT NULL,
    EquipmentId     INTEGER       NOT NULL,
    UserId          INTEGER       NOT NULL,
    ReservationDate DATE          NOT NULL,                      -- 'YYYY-MM-DD'
    StartTime       NVARCHAR(5)   NOT NULL,                      -- 'HH:MM'
    Duration        INTEGER       NOT NULL DEFAULT 60,           -- minutes
    Status          NVARCHAR(20)  NOT NULL DEFAULT 'reserved',   -- 'reserved' | 'cancelled' | 'completed'
    CreatedAt       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_EquipmentReservation PRIMARY KEY (ReservationId),
    CONSTRAINT CK_EquipmentReservation_Status CHECK (Status IN ('reserved','cancelled','completed')),
    FOREIGN KEY (EquipmentId) REFERENCES Equipment (EquipmentId) ON DELETE CASCADE,
    FOREIGN KEY (UserId)      REFERENCES User      (UserId)      ON DELETE CASCADE
);

-- A member cannot hold two active reservations for the same equipment + date + slot.
-- (Overall slot capacity vs Equipment.Quantity is enforced in application code.)
CREATE UNIQUE INDEX UQ_EquipmentReservation_Slot
    ON EquipmentReservation (EquipmentId, UserId, ReservationDate, StartTime)
    WHERE Status = 'reserved';

/*******************************************************************************
   Member progress: fitness goals (target/current, tracked over time)
********************************************************************************/

CREATE TABLE FitnessGoal (
    GoalId       INTEGER       NOT NULL,
    UserId       INTEGER       NOT NULL,
    Title        NVARCHAR(120) NOT NULL,
    Metric       NVARCHAR(40)  NOT NULL DEFAULT 'custom',  -- 'weight' | 'workouts_per_week' | 'calories' | 'duration' | 'custom'
    TargetValue  REAL          NOT NULL,
    CurrentValue REAL          NOT NULL DEFAULT 0,
    Unit         NVARCHAR(20),
    Deadline     DATE,
    Status       NVARCHAR(20)  NOT NULL DEFAULT 'active',  -- 'active' | 'achieved' | 'abandoned'
    CreatedAt    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_FitnessGoal PRIMARY KEY (GoalId),
    CONSTRAINT CK_FitnessGoal_Status CHECK (Status IN ('active','achieved','abandoned')),
    FOREIGN KEY (UserId) REFERENCES User (UserId) ON DELETE CASCADE
);

/*******************************************************************************
   Nutrition: per-meal breakdown of a plan + daily consumption log
********************************************************************************/

CREATE TABLE NutritionMeal (
    MealId    INTEGER       NOT NULL,
    PlanId    INTEGER       NOT NULL,                 -- FK NutritionPlan.PlanId
    Name      NVARCHAR(80)  NOT NULL,                 -- 'Breakfast', 'Lunch', ...
    Calories  INTEGER       NOT NULL DEFAULT 0,
    Protein   INTEGER       NOT NULL DEFAULT 0,
    Carbs     INTEGER       NOT NULL DEFAULT 0,
    Fats      INTEGER       NOT NULL DEFAULT 0,
    MealOrder INTEGER       NOT NULL DEFAULT 0,
    CONSTRAINT PK_NutritionMeal PRIMARY KEY (MealId),
    FOREIGN KEY (PlanId) REFERENCES NutritionPlan (PlanId) ON DELETE CASCADE
);

CREATE TABLE NutritionLog (
    LogId     INTEGER       NOT NULL,
    UserId    INTEGER       NOT NULL,
    LogDate   DATE          NOT NULL,                 -- 'YYYY-MM-DD'
    Calories  INTEGER       NOT NULL DEFAULT 0,
    Protein   INTEGER       NOT NULL DEFAULT 0,
    Carbs     INTEGER       NOT NULL DEFAULT 0,
    Fats      INTEGER       NOT NULL DEFAULT 0,
    Note      NVARCHAR(140),
    CreatedAt DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT PK_NutritionLog PRIMARY KEY (LogId),
    FOREIGN KEY (UserId) REFERENCES User (UserId) ON DELETE CASCADE
);

CREATE INDEX IFK_EquipmentReservationUser  ON EquipmentReservation (UserId);
CREATE INDEX IFK_EquipmentReservationEquip ON EquipmentReservation (EquipmentId);
CREATE INDEX IFK_FitnessGoalUser           ON FitnessGoal          (UserId);
CREATE INDEX IFK_NutritionMealPlan         ON NutritionMeal        (PlanId);
CREATE INDEX IFK_NutritionLogUser          ON NutritionLog         (UserId);

/*******************************************************************************
   Create Indexes (foreign-key access paths)
********************************************************************************/

CREATE INDEX IFK_TrainerUser         ON Trainer    (TrainerId);
CREATE INDEX IFK_ClassTrainer        ON Class      (TrainerId);
CREATE INDEX IFK_ClassLocation       ON Class      (LocationId);
CREATE INDEX IFK_ScheduleClass       ON Schedule   (ClassId);
CREATE INDEX IFK_EnrollmentUser      ON Enrollment (UserId);
CREATE INDEX IFK_EnrollmentSchedule  ON Enrollment (ScheduleId);
CREATE INDEX IFK_EquipmentLocation   ON Equipment  (LocationId);
CREATE INDEX IFK_ReviewClass         ON Review     (ClassId);
CREATE INDEX IFK_WorkoutUser         ON Workout    (UserId);
CREATE INDEX IFK_WaitlistSchedule    ON Waitlist     (ScheduleId);
CREATE INDEX IFK_WaitlistUser        ON Waitlist     (UserId);
CREATE INDEX IFK_NotificationUser    ON Notification (UserId);
CREATE INDEX IFK_MembershipUser      ON Membership          (UserId);
CREATE INDEX IFK_MembershipPlan      ON Membership          (PlanId);
CREATE INDEX IFK_AvailabilityTrainer ON TrainerAvailability (TrainerId);
CREATE INDEX IFK_BookingUser         ON Booking             (UserId);
CREATE INDEX IFK_BookingTrainer      ON Booking             (TrainerId);
CREATE INDEX IFK_BookingAvailability ON Booking             (AvailabilityId);
CREATE INDEX IFK_DisputeUser         ON Dispute             (UserId);
CREATE INDEX IFK_NutritionPlanUser   ON NutritionPlan       (UserId);
CREATE INDEX IFK_ApplicationUser     ON MembershipApplication (UserId);
CREATE INDEX IFK_ApplicationPlan     ON MembershipApplication (PlanId);

/*******************************************************************************
   Populate Tables
   (Default password for every seeded account: "password")
********************************************************************************/

-- Locations -------------------------------------------------------------------
INSERT INTO Location (LocationId, Name, City, Country, Address, Photo) VALUES
(1, 'Iron Haven New York', 'New York', 'United States', '142 Broome Street, NY 10002', 'location-new-york.jpg'),
(2, 'Iron Haven Lisbon',   'Lisbon',   'Portugal',      'Av. da Liberdade 211, 1250-142 Lisboa', 'location-lisbon.jpg'),
(3, 'Iron Haven Porto',    'Porto',    'Portugal',      'Rua de Cedofeita 410, 4050-127 Porto',  'location-porto.jpg'),
(4, 'Iron Haven Viseu',    'Viseu',    'Portugal',      'Rua Direita 88, 3500-103 Viseu',        'location-viseu.jpg');

-- Users (admin + trainers + members) ------------------------------------------
-- Admin
INSERT INTO User (UserId, FirstName, LastName, Username, Email, Password, Photo, Role) VALUES
(1, 'Sofia', 'Admin', 'admin', 'admin@ironhaven.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin');

-- Trainers (UserId 2..10 reserved for trainers)
INSERT INTO User (UserId, FirstName, LastName, Username, Email, Password, Photo, Role) VALUES
(2,  'Anika',   'Patel',     'apatel',     'anika.patel@ironhaven.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-anika-patel.jpg',    'trainer'),
(3,  'Clara',   'Fontes',    'cfontes',    'clara.fontes@ironhaven.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-clara-fontes.jpg',   'trainer'),
(4,  'Diogo',   'Almeida',   'dalmeida',   'diogo.almeida@ironhaven.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-diogo-almeida.jpg',  'trainer'),
(5,  'Elena',   'Costa',     'ecosta',     'elena.costa@ironhaven.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-elena-costa.jpg',    'trainer'),
(6,  'Henrik',  'Vogel',     'hvogel',     'henrik.vogel@ironhaven.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-henrik-vogel.jpg',   'trainer'),
(7,  'James',   'Okafor',    'jokafor',    'james.okafor@ironhaven.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-james-okafor.jpg',   'trainer'),
(8,  'Marcus',  'Reid',      'mreid',      'marcus.reid@ironhaven.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-marcus-reid.jpg',    'trainer'),
(9,  'Sofia',   'Marchetti', 'smarchetti', 'sofia.marchetti@ironhaven.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-sofia-marchetti.jpg','trainer'),
(10, 'Yuki',    'Tanaka',    'ytanaka',    'yuki.tanaka@ironhaven.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer-yuki-tanaka.jpg',    'trainer');

-- Members
INSERT INTO User (UserId, FirstName, LastName, Username, Email, Password, Photo, Role) VALUES
(11, 'Jordan', 'Miller',  'jmiller',  'jordan.miller@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'member'),
(12, 'Amelia', 'Rocha',   'arocha',   'amelia.rocha@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'member'),
(13, 'Carlos', 'Faria',   'cfaria',   'carlos.faria@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'member'),
(14, 'Beatriz','Santos',  'bsantos',  'beatriz.santos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'member');

-- Trainer extension rows -------------------------------------------------------
INSERT INTO Trainer (TrainerId, Bio, Specialty, Certifications) VALUES
(2,  'Mobility specialist focused on long-term joint health.',            'Mobility',          'NASM-CPT, FRCms'),
(3,  'Olympic lifting and powerlifting coach with 12 years of experience.','Strength',          'USAW L2, CSCS'),
(4,  'Functional training and HIIT programming for time-poor executives.','Functional / HIIT', 'ACE-CPT, PN1'),
(5,  'Pilates and core conditioning with a rehab-first approach.',         'Pilates',           'STOTT Pilates, PMA-CPT'),
(6,  'Endurance and triathlon coach. Former competitive cyclist.',         'Endurance',         'NSCA-CSCS, USAT L1'),
(7,  'Boxing and combat-conditioning. Discreet PT for high-profile clients.','Boxing',          'USA Boxing Coach, NASM-CPT'),
(8,  'Hypertrophy and aesthetic programming. Recovery-driven approach.',   'Hypertrophy',       'NSCA-CPT, PN1'),
(9,  'Yoga, breathwork and recovery flows in a private studio setting.',   'Yoga / Recovery',   'RYT-500, PMA-CPT'),
(10, 'Mind-body conditioning blending martial arts and mobility work.',    'Martial Arts',      'BJJ Black Belt, NASM-CPT');

-- Classes ---------------------------------------------------------------------
INSERT INTO Class (ClassId, Name, Type, Description, TrainerId, LocationId, Capacity) VALUES
(1, 'Heavy Squat Lab',         'Strength',   'Coached barbell squat session with individualised loading and form review.', 3,  1, 8),
(2, 'Mobility Flow',            'Mobility',   'Joint articulation and tissue prep for high performers.',                    2,  2, 10),
(3, 'Executive HIIT',           'HIIT',       'Forty-five minutes of metabolic conditioning, programmed and supervised.',   4,  1, 12),
(4, 'Reformer Pilates',         'Pilates',    'Reformer-based core and stabiliser conditioning in a private studio.',       5,  2, 6),
(5, 'Endurance Lab',            'Endurance',  'Threshold and VO2 work on bike, rower and ski erg.',                         6,  3, 8),
(6, 'Private Boxing Session',   'Boxing',     'Coached pad-work and technique. Discreet, members-only setting.',            7,  1, 4),
(7, 'Hypertrophy Block',        'Hypertrophy','Push/pull/legs split, coached and tracked end-to-end.',                      8,  3, 8),
(8, 'Recovery Yoga',            'Recovery',   'Slow yoga and breathwork for parasympathetic recovery.',                     9,  4, 12),
(9, 'Combat Conditioning',      'Martial Arts','Striking, grappling drills and conditioning intervals.',                   10,  4, 8);

-- Schedules -------------------------------------------------------------------
INSERT INTO Schedule (ScheduleId, ClassId, DayOfWeek, StartTime, Duration) VALUES
(1,  1, 'Monday',    '18:00', 60),
(2,  1, 'Thursday',  '07:00', 60),
(3,  2, 'Tuesday',   '08:30', 45),
(4,  2, 'Friday',    '17:30', 45),
(5,  3, 'Monday',    '06:30', 45),
(6,  3, 'Wednesday', '12:15', 45),
(7,  4, 'Wednesday', '10:00', 50),
(8,  5, 'Tuesday',   '06:30', 75),
(9,  5, 'Saturday',  '09:00', 90),
(10, 6, 'Thursday',  '19:00', 60),
(11, 7, 'Monday',    '17:00', 75),
(12, 7, 'Friday',    '17:00', 75),
(13, 8, 'Sunday',    '10:00', 60),
(14, 9, 'Saturday',  '11:00', 60);

-- Enrollments -----------------------------------------------------------------
INSERT INTO Enrollment (EnrollmentId, UserId, ScheduleId, Status) VALUES
(1, 11, 1, 'enrolled'),
(2, 11, 5, 'enrolled'),
(3, 12, 3, 'enrolled'),
(4, 12, 7, 'enrolled'),
(5, 13, 11,'enrolled'),
(6, 14, 13,'enrolled'),
(7, 14, 8, 'cancelled');

-- Equipment -------------------------------------------------------------------
INSERT INTO Equipment (EquipmentId, Name, Type, LocationId, Quantity, Available, Status) VALUES
(1,  'Eleiko Olympic Barbell',     'Free Weights', 1, 6,  6, 'operational'),
(2,  'Concept2 RowErg',            'Cardio',       1, 4,  3, 'operational'),
(3,  'Assault AirBike',            'Cardio',       1, 3,  3, 'operational'),
(4,  'Power Rack',                 'Strength',     1, 4,  4, 'operational'),
(5,  'Reformer (Allegro 2)',       'Pilates',      2, 6,  6, 'operational'),
(6,  'Adjustable Dumbbells (Set)', 'Free Weights', 2, 8,  7, 'operational'),
(7,  'Watt Bike Atom',             'Cardio',       3, 5,  4, 'operational'),
(8,  'Ski Erg',                    'Cardio',       3, 3,  3, 'operational'),
(9,  'Boxing Ring',                'Combat',       1, 1,  1, 'operational'),
(10, 'Cold Plunge',                'Recovery',     4, 2,  1, 'operational'),
(11, 'Infrared Sauna',             'Recovery',     4, 2,  2, 'operational'),
(12, 'Cable Crossover',            'Strength',     3, 2,  2, 'maintenance');

-- Reviews ---------------------------------------------------------------------
INSERT INTO Review (ReviewId, UserId, ClassId, Rating, Comment) VALUES
(1, 11, 1, 5, 'Outstanding coaching, programming was spot on for my lift.'),
(2, 12, 4, 5, 'The reformer studio is immaculate and the session was world class.'),
(3, 13, 7, 4, 'Solid hypertrophy block — would prefer a slightly smaller group size.'),
(4, 14, 8, 5, 'Recovery yoga left me genuinely reset. Worth the membership alone.');

-- Workouts (member progress log) ----------------------------------------------
INSERT INTO Workout (WorkoutId, UserId, WorkoutType, DurationMinutes, Calories, Status) VALUES
(1, 11, 'Strength',  75, 540, 'Completed'),
(2, 11, 'HIIT',      45, 510, 'Completed'),
(3, 12, 'Pilates',   50, 280, 'Completed'),
(4, 13, 'Strength',  80, 600, 'Completed'),
(5, 14, 'Recovery',  60, 180, 'Completed');

-- Membership plans ------------------------------------------------------------
INSERT INTO Plan (PlanId, Name, Tier, Price, Period, Description, Featured, PTSessionsLimit) VALUES
(1, 'Essential', 1, 450,  'per month', 'Full gym floor access, group classes, locker & towel service at one home location.', 0, 0),
(2, 'Premium',   2, 950,  'per month', 'Everything in Essential plus personal training, recovery & spa access, and multi-location access.', 1, 4),
(3, 'Elite',     3, 2400, 'per month', 'Unlimited personal training, private suite reservation, dedicated concierge and global access.', 0, -1);

-- Current member subscriptions ------------------------------------------------
INSERT INTO Membership (MembershipId, UserId, PlanId, Status, RenewsAt) VALUES
(1, 11, 2, 'active', datetime('now', '+1 month')),
(2, 12, 1, 'active', datetime('now', '+1 month')),
(3, 13, 3, 'active', datetime('now', '+1 month'));

-- Membership applications (a member applies for a plan; admin accepts/rejects) -
-- Beatriz (14) has no active plan: one pending application for Premium to triage.
INSERT INTO MembershipApplication (ApplicationId, UserId, PlanId, Message, Status, DecidedAt, DecidedBy) VALUES
(1, 14, 2, 'I''d love to move up to Premium for the personal training sessions.', 'pending', NULL, NULL);

-- Trainer availability (recurring weekly one-on-one slots) --------------------
INSERT INTO TrainerAvailability (AvailabilityId, TrainerId, DayOfWeek, StartTime, Duration) VALUES
(1,  3, 'Monday',    '09:00', 60),
(2,  3, 'Wednesday', '09:00', 60),
(3,  3, 'Friday',    '14:00', 60),
(4,  2, 'Tuesday',   '10:00', 60),
(5,  2, 'Thursday',  '10:00', 60),
(6,  4, 'Monday',    '07:00', 45),
(7,  4, 'Wednesday', '18:00', 45),
(8,  7, 'Thursday',  '20:00', 60),
(9,  8, 'Tuesday',   '16:00', 60),
(10, 9, 'Sunday',    '11:00', 60);

-- An existing personal-training booking ---------------------------------------
INSERT INTO Booking (BookingId, UserId, TrainerId, AvailabilityId, BookingDate, Status, Notes) VALUES
(1, 11, 3, 1, date('now', 'weekday 1'), 'booked', 'Focus on squat depth.');

-- Disputes & feedback ---------------------------------------------------------
INSERT INTO Dispute (DisputeId, UserId, Category, Subject, Message, Status, Response, RespondedBy) VALUES
(1, 11, 'equipment', 'Cable crossover out of service', 'The cable crossover in Porto has been down for two weeks.', 'in_review', NULL, NULL),
(2, 12, 'class', 'Reformer class overbooked', 'My Wednesday reformer session felt over capacity last week.', 'resolved', 'Apologies — capacity has been corrected and a spot reserved for you.', 1),
(3, 14, 'billing', 'Double charge in May', 'I appear to have been billed twice for my May membership.', 'open', NULL, NULL);

/*******************************************************************************
   Seed data for new feature slices
   (promotions, equipment reservations, fitness goals, nutrition meals/logs)
********************************************************************************/

-- Promotional Features: highlight a couple of classes and trainers on the homepage
UPDATE Class   SET Featured = 1 WHERE ClassId   IN (1, 3);
UPDATE Trainer SET Featured = 1 WHERE TrainerId IN (3, 2);

-- Equipment reservations (Jordan reserves a Power Rack and a Cold Plunge slot)
INSERT INTO EquipmentReservation (ReservationId, EquipmentId, UserId, ReservationDate, StartTime, Duration, Status) VALUES
(1, 4,  11, date('now', '+1 day'), '18:00', 60, 'reserved'),
(2, 10, 11, date('now', '+2 day'), '07:30', 30, 'reserved');

-- Fitness goals (Jordan)
INSERT INTO FitnessGoal (GoalId, UserId, Title, Metric, TargetValue, CurrentValue, Unit, Deadline, Status) VALUES
(1, 11, 'Squat 140kg',          'weight',            140, 120, 'kg',       date('now', '+2 month'), 'active'),
(2, 11, 'Train 4x per week',    'workouts_per_week', 4,   2,   'sessions', date('now', '+1 month'), 'active'),
(3, 12, 'Reach 1800 kcal/day',  'calories',          1800,2000,'kcal',     date('now', '+1 month'), 'active');

-- A nutrition plan for Jordan (11) with a per-meal breakdown, assigned by trainer Clara (3)
INSERT INTO NutritionPlan (PlanId, UserId, DietType, DailyCalories, Protein, Carbs, Fats, Guidelines) VALUES
(1, 11, 'High Protein', 2400, 190, 230, 70, 'Prioritise protein at every meal. Hydrate to 3L/day. Carbs around training.');

INSERT INTO NutritionMeal (MealId, PlanId, Name, Calories, Protein, Carbs, Fats, MealOrder) VALUES
(1, 1, 'Breakfast',     520, 40, 55, 14, 1),
(2, 1, 'Lunch',         700, 55, 70, 20, 2),
(3, 1, 'Pre-Training',  300, 20, 45, 5,  3),
(4, 1, 'Dinner',        680, 60, 45, 24, 4),
(5, 1, 'Evening Snack', 200, 15, 15, 7,  5);

-- A few days of consumption logs for Jordan (so progress charts have data)
INSERT INTO NutritionLog (LogId, UserId, LogDate, Calories, Protein, Carbs, Fats, Note) VALUES
(1, 11, date('now', '-3 day'), 2310, 185, 210, 66, 'On track'),
(2, 11, date('now', '-2 day'), 2480, 195, 240, 72, 'Refeed day'),
(3, 11, date('now', '-1 day'), 2260, 180, 205, 64, NULL);
