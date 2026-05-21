/*******************************************************************************
   Iron Haven Database
   DB Server: SQLite
   Default password for all seeded accounts: "password"
   (bcrypt hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
********************************************************************************/

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
    Username    NVARCHAR(40)  NOT NULL,
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
