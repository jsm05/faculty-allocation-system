-- ============================================================
--  FACULTY COURSE ALLOCATION SYSTEM
--  DBMS Group Project — Complete SQL Setup File
--  Run this entire file in MySQL Workbench once.
--  It will create the database, tables, data, triggers,
--  views, stored procedure, and all 3 user accounts.
-- ============================================================


-- ============================================================
--  STEP 1: CREATE & SELECT DATABASE
-- ============================================================

DROP DATABASE IF EXISTS faculty_allocation_db;
CREATE DATABASE faculty_allocation_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE faculty_allocation_db;


-- ============================================================
--  STEP 2: CREATE TABLES (as per ER Diagram)
-- ============================================================

-- Person 3 — Department table
CREATE TABLE Department (
    dept_id    INT          PRIMARY KEY AUTO_INCREMENT,
    dept_name  VARCHAR(100) NOT NULL UNIQUE
);

-- Person 3 — Semester table
CREATE TABLE Semester (
    semester_id    INT         PRIMARY KEY AUTO_INCREMENT,
    semester_no    INT         NOT NULL,
    academic_year  VARCHAR(10) NOT NULL,
    CONSTRAINT chk_sem_no CHECK (semester_no BETWEEN 1 AND 8),
    CONSTRAINT uq_sem UNIQUE (semester_no, academic_year)
);

-- Person 1 — Faculty table (depends on Department)
CREATE TABLE Faculty (
    faculty_id    INT          PRIMARY KEY AUTO_INCREMENT,
    faculty_name  VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    designation   VARCHAR(50)  NOT NULL DEFAULT 'Lecturer',
    max_workload  INT          NOT NULL DEFAULT 16,
    dept_id       INT,
    CONSTRAINT chk_workload CHECK (max_workload BETWEEN 4 AND 30),
    CONSTRAINT chk_designation CHECK (
        designation IN ('Professor','Associate Professor','Assistant Professor','Lecturer')
    ),
    CONSTRAINT fk_faculty_dept FOREIGN KEY (dept_id)
        REFERENCES Department(dept_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- Person 2 — Course table (depends on Department, Semester)
CREATE TABLE Course (
    course_id    INT          PRIMARY KEY AUTO_INCREMENT,
    course_code  VARCHAR(10)  NOT NULL UNIQUE,
    course_name  VARCHAR(150) NOT NULL,
    credits      INT          NOT NULL,
    course_type  ENUM('Theory','Lab','Theory+Lab') NOT NULL DEFAULT 'Theory',
    dept_id      INT,
    semester_id  INT,
    CONSTRAINT chk_credits CHECK (credits BETWEEN 1 AND 6),
    CONSTRAINT chk_course_code CHECK (course_code REGEXP '^[A-Z]{2,4}[0-9]{3,4}$'),
    CONSTRAINT fk_course_dept FOREIGN KEY (dept_id)
        REFERENCES Department(dept_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_course_sem FOREIGN KEY (semester_id)
        REFERENCES Semester(semester_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- Person 4 — Allocation table (depends on Faculty, Course, Semester)
CREATE TABLE Allocation (
    allocation_id   INT PRIMARY KEY AUTO_INCREMENT,
    faculty_id      INT NOT NULL,
    course_id       INT NOT NULL,
    semester_id     INT NOT NULL,
    assigned_hours  INT NOT NULL,
    CONSTRAINT chk_hours CHECK (assigned_hours > 0),
    CONSTRAINT uq_allocation UNIQUE (faculty_id, course_id, semester_id),
    CONSTRAINT fk_alloc_faculty FOREIGN KEY (faculty_id)
        REFERENCES Faculty(faculty_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_alloc_course FOREIGN KEY (course_id)
        REFERENCES Course(course_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_alloc_sem FOREIGN KEY (semester_id)
        REFERENCES Semester(semester_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- ============================================================
--  STEP 3: INSERT SAMPLE DATA
-- ============================================================

-- Departments
INSERT INTO Department (dept_name) VALUES
    ('Computer Science'),
    ('Electronics'),
    ('Mechanical');

-- Semesters
INSERT INTO Semester (semester_no, academic_year) VALUES
    (1, '2023-24'),
    (2, '2023-24'),
    (3, '2023-24');

-- Faculty (Person 1 module)
INSERT INTO Faculty (faculty_name, email, designation, max_workload, dept_id) VALUES
    ('Dr. Ananya Sharma',   'ananya.sharma@college.edu',   'Professor',           20, 1),
    ('Prof. Rajan Nair',    'rajan.nair@college.edu',      'Associate Professor',  18, 1),
    ('Dr. Priya Menon',     'priya.menon@college.edu',     'Professor',           20, 2),
    ('Prof. Suresh Kamath', 'suresh.kamath@college.edu',   'Assistant Professor', 16, 2),
    ('Dr. Kavita Joshi',    'kavita.joshi@college.edu',    'Professor',           20, 3),
    ('Prof. Anil Desai',    'anil.desai@college.edu',      'Assistant Professor', 16, 3),
    ('Dr. Meera Iyer',      'meera.iyer@college.edu',      'Assistant Professor', 14, 1),
    ('Prof. Sanjay Rao',    'sanjay.rao@college.edu',      'Lecturer',            12, 2);

-- Courses (Person 2 module)
INSERT INTO Course (course_code, course_name, credits, course_type, dept_id, semester_id) VALUES
    -- Computer Science
    ('CS101', 'Data Structures',              4, 'Theory',     1, 1),
    ('CS102', 'Data Structures Lab',          2, 'Lab',        1, 1),
    ('CS201', 'Database Management Systems',  4, 'Theory',     1, 2),
    ('CS202', 'DBMS Lab',                     2, 'Lab',        1, 2),
    ('CS301', 'Operating Systems',            4, 'Theory',     1, 3),
    ('CS302', 'Computer Networks',            3, 'Theory',     1, 3),
    ('CS303', 'Computer Networks Lab',        2, 'Lab',        1, 3),
    ('CS304', 'Software Engineering',         3, 'Theory+Lab', 1, 3),
    -- Electronics
    ('EC101', 'Circuit Theory',               4, 'Theory',     2, 1),
    ('EC102', 'Basic Electronics Lab',        2, 'Lab',        2, 1),
    ('EC201', 'Digital Electronics',          4, 'Theory',     2, 2),
    ('EC202', 'Digital Electronics Lab',      2, 'Lab',        2, 2),
    ('EC301', 'Signals and Systems',          3, 'Theory',     2, 3),
    -- Mechanical
    ('ME101', 'Engineering Mechanics',        4, 'Theory',     3, 1),
    ('ME102', 'Workshop Practice',            2, 'Lab',        3, 1),
    ('ME201', 'Thermodynamics',               4, 'Theory',     3, 2),
    ('ME301', 'Fluid Mechanics',              3, 'Theory',     3, 3);

-- Allocations (Person 4 module)
INSERT INTO Allocation (faculty_id, course_id, semester_id, assigned_hours) VALUES
    (1, 1,  1, 4),   -- Dr. Ananya → Data Structures
    (1, 3,  2, 4),   -- Dr. Ananya → DBMS
    (2, 5,  3, 4),   -- Prof. Rajan → OS
    (2, 6,  3, 3),   -- Prof. Rajan → Networks
    (3, 9,  1, 4),   -- Dr. Priya → Circuit Theory
    (3, 11, 2, 4),   -- Dr. Priya → Digital Electronics
    (4, 10, 1, 2),   -- Prof. Suresh → Electronics Lab
    (4, 12, 2, 2),   -- Prof. Suresh → Digital Lab
    (5, 14, 1, 4),   -- Dr. Kavita → Engineering Mechanics
    (5, 16, 2, 4),   -- Dr. Kavita → Thermodynamics
    (6, 15, 1, 2),   -- Prof. Anil → Workshop
    (7, 8,  3, 3);   -- Dr. Meera → Software Engineering


-- ============================================================
--  STEP 4: TRIGGERS (Person 6 module)
-- ============================================================

DELIMITER $$

-- Trigger 1: Prevent faculty workload from exceeding max_workload
CREATE TRIGGER trg_check_workload
BEFORE INSERT ON Allocation
FOR EACH ROW
BEGIN
    DECLARE v_current_hours INT DEFAULT 0;
    DECLARE v_max_hours     INT DEFAULT 0;

    SELECT COALESCE(SUM(assigned_hours), 0)
      INTO v_current_hours
      FROM Allocation
     WHERE faculty_id = NEW.faculty_id;

    SELECT max_workload
      INTO v_max_hours
      FROM Faculty
     WHERE faculty_id = NEW.faculty_id;

    IF (v_current_hours + NEW.assigned_hours) > v_max_hours THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ERROR: Allocation blocked — faculty workload would exceed maximum limit.';
    END IF;
END$$

-- Trigger 2: Block duplicate faculty-course allocation in same semester
CREATE TRIGGER trg_no_duplicate_allocation
BEFORE INSERT ON Allocation
FOR EACH ROW
BEGIN
    DECLARE v_count INT DEFAULT 0;

    SELECT COUNT(*)
      INTO v_count
      FROM Allocation
     WHERE faculty_id  = NEW.faculty_id
       AND course_id   = NEW.course_id
       AND semester_id = NEW.semester_id;

    IF v_count > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ERROR: Duplicate allocation — faculty already assigned to this course in this semester.';
    END IF;
END$$

DELIMITER ;


-- ============================================================
--  STEP 5: STORED PROCEDURE WITH TRANSACTION (Person 6)
-- ============================================================

DELIMITER $$

CREATE PROCEDURE sp_allocate_faculty (
    IN p_faculty_id   INT,
    IN p_course_id    INT,
    IN p_semester_id  INT,
    IN p_hours        INT
)
BEGIN
    -- Exit handler: rollback on any SQL error
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
        INSERT INTO Allocation (faculty_id, course_id, semester_id, assigned_hours)
        VALUES (p_faculty_id, p_course_id, p_semester_id, p_hours);
    COMMIT;

    SELECT CONCAT('Success: Faculty ID ', p_faculty_id,
                  ' allocated to Course ID ', p_course_id,
                  ' for ', p_hours, ' hours.') AS result;
END$$

DELIMITER ;


-- ============================================================
--  STEP 6: VIEWS (Person 7 module)
-- ============================================================

-- View 1: Full faculty report with workload summary
CREATE VIEW vw_faculty_report AS
SELECT
    f.faculty_id,
    f.faculty_name,
    f.email,
    f.designation,
    d.dept_name,
    COUNT(a.allocation_id)            AS courses_assigned,
    COALESCE(SUM(a.assigned_hours),0) AS total_hours,
    f.max_workload,
    CASE
        WHEN COALESCE(SUM(a.assigned_hours),0) > f.max_workload     THEN 'Overloaded'
        WHEN COALESCE(SUM(a.assigned_hours),0) >= f.max_workload*0.7 THEN 'Normal'
        WHEN COALESCE(SUM(a.assigned_hours),0) > 0                  THEN 'Under-utilized'
        ELSE 'Unassigned'
    END AS load_status
FROM Faculty f
JOIN Department d        ON f.dept_id    = d.dept_id
LEFT JOIN Allocation a   ON f.faculty_id = a.faculty_id
GROUP BY f.faculty_id, f.faculty_name, f.email, f.designation,
         d.dept_name, f.max_workload;

-- View 2: Full course allocation report
CREATE VIEW vw_course_report AS
SELECT
    c.course_id,
    c.course_code,
    c.course_name,
    c.course_type,
    c.credits,
    d.dept_name,
    s.semester_no,
    s.academic_year,
    f.faculty_name,
    a.assigned_hours,
    CASE WHEN a.allocation_id IS NULL THEN 'Unallocated' ELSE 'Allocated' END AS status
FROM Course c
LEFT JOIN Department d   ON c.dept_id     = d.dept_id
LEFT JOIN Semester s     ON c.semester_id = s.semester_id
LEFT JOIN Allocation a   ON c.course_id   = a.course_id
LEFT JOIN Faculty f      ON a.faculty_id  = f.faculty_id;

-- View 3: Department-wise summary
CREATE VIEW vw_dept_summary AS
SELECT
    d.dept_name,
    COUNT(DISTINCT c.course_id)    AS total_courses,
    SUM(c.credits)                 AS total_credits,
    COUNT(DISTINCT f.faculty_id)   AS faculty_count,
    COUNT(DISTINCT a.allocation_id) AS total_allocations,
    ROUND(AVG(a.assigned_hours),1) AS avg_load_per_allocation
FROM Department d
LEFT JOIN Course     c ON d.dept_id    = c.dept_id
LEFT JOIN Faculty    f ON d.dept_id    = f.dept_id
LEFT JOIN Allocation a ON f.faculty_id = a.faculty_id
GROUP BY d.dept_id, d.dept_name;


-- ============================================================
--  STEP 7: DBA USER ACCOUNTS & PRIVILEGES (Person 7 / DBA)
--
--  NOTE: Run these lines separately if you get a permissions
--  error — you must be logged in as root in Workbench.
-- ============================================================

-- Drop users if they already exist (safe re-run)
DROP USER IF EXISTS 'admin_dba'@'localhost';
DROP USER IF EXISTS 'editor_user'@'localhost';
DROP USER IF EXISTS 'viewer_user'@'localhost';

-- User 1: Admin — full DBA rights including CREATE USER
CREATE USER 'admin_dba'@'localhost' IDENTIFIED BY 'admin123';
GRANT ALL PRIVILEGES ON faculty_allocation_db.* TO 'admin_dba'@'localhost';
GRANT CREATE USER ON *.* TO 'admin_dba'@'localhost' WITH GRANT OPTION;

-- User 2: Editor — view + update only, NO create user, NO delete, NO drop
CREATE USER 'editor_user'@'localhost' IDENTIFIED BY 'edit456';
GRANT SELECT, INSERT, UPDATE ON faculty_allocation_db.* TO 'editor_user'@'localhost';

-- User 3: Viewer — read only (SELECT only)
CREATE USER 'viewer_user'@'localhost' IDENTIFIED BY 'view789';
GRANT SELECT ON faculty_allocation_db.* TO 'viewer_user'@'localhost';

FLUSH PRIVILEGES;


-- ============================================================
--  STEP 8: VERIFY — RUN THESE TO CONFIRM EVERYTHING WORKS
-- ============================================================

-- Check all tables exist
SHOW TABLES;

-- Check all data
SELECT * FROM Department;
SELECT * FROM Semester;
SELECT * FROM Faculty;
SELECT * FROM Course;
SELECT * FROM Allocation;

-- Check views work
SELECT * FROM vw_faculty_report;
SELECT * FROM vw_course_report;
SELECT * FROM vw_dept_summary;

-- Check user privileges
SHOW GRANTS FOR 'admin_dba'@'localhost';
SHOW GRANTS FOR 'editor_user'@'localhost';
SHOW GRANTS FOR 'viewer_user'@'localhost';

-- Test the stored procedure (allocates Dr. Meera to CS302 Networks)
-- CALL sp_allocate_faculty(7, 6, 3, 3);

-- Test trigger: this should FAIL with workload error
-- INSERT INTO Allocation (faculty_id, course_id, semester_id, assigned_hours)
-- VALUES (1, 2, 1, 99);

-- Test trigger: this should FAIL with duplicate error
-- INSERT INTO Allocation (faculty_id, course_id, semester_id, assigned_hours)
-- VALUES (1, 1, 1, 4);

-- ============================================================
--  SETUP COMPLETE. Your database is ready.
-- ============================================================
