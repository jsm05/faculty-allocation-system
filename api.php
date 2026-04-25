<?php
ini_set('display_errors', 0);
error_reporting(0);

// ─── MUST come first: turn off mysqli exceptions + set JSON error handler ─────
mysqli_report(MYSQLI_REPORT_OFF);
header("Content-Type: application/json");
set_exception_handler(function($e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
});

session_start();

// ─── Auth guard ───────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_role'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized. Please log in."]);
    exit;
}

$role    = $_SESSION['user_role'];
$db_user = $_SESSION['db_user'] ?? 'root';

// ─── Role capability flags ────────────────────────────────────────────────────
$can_insert = in_array($role, ['admin', 'editor']);
$can_delete = ($role === 'admin');
$can_ddl    = ($role === 'admin');

// ─── DB connection — tries session user first, falls back to root ─────────────
$host     = "localhost";
$pass_map = [
    'admin_dba'   => 'admin123',
    'editor_user' => 'edit456',
    'viewer_user' => 'view789',
    'root'        => '',
];
$db_pass = $pass_map[$db_user] ?? '';
$conn = @new mysqli($host, $db_user, $db_pass, "faculty_allocation_db");

// If role-based user fails, fall back to root (handles case where SQL users not yet created)
if ($conn->connect_error) {
    $conn = @new mysqli($host, 'root', '', "faculty_allocation_db");
    if ($conn->connect_error) {
        echo json_encode(["status" => "error", "message" => "DB connection failed. Is XAMPP MySQL running?"]);
        exit;
    }
}

$action = $_GET['action'] ?? '';

switch ($action) {

// ─── FACULTY ──────────────────────────────────────────────
case "get_faculty":
    $res = $conn->query("
        SELECT f.*, d.dept_name
        FROM Faculty f
        JOIN Department d ON f.dept_id = d.dept_id
    ");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    break;

case "add_faculty":
    if (!$can_insert) { echo json_encode(["status"=>"error","message"=>"Permission denied: your role cannot insert records."]); break; }
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        echo json_encode(["status" => "error", "message" => "No JSON received"]);
        exit;
    }
    $stmt = $conn->prepare("
        INSERT INTO Faculty (faculty_name, email, designation, max_workload, dept_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        break;
    }
    $stmt->bind_param("sssii",
        $data['faculty_name'], $data['email'],
        $data['designation'], $data['max_workload'], $data['dept_id']
    );
    $result = $stmt->execute();
    if ($result) {
        echo json_encode(["status" => "success"]);
    } else {
        if ($conn->errno == 1062) {
            echo json_encode(["status" => "error", "message" => "A faculty member with this email already exists. Email must be unique."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error " . $conn->errno . ": " . $conn->error]);
        }
    }
    break;

// ─── COURSES ──────────────────────────────────────────────
case "get_courses":
    $res = $conn->query("SELECT * FROM Course");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    break;

case "add_course":
    if (!$can_insert) { echo json_encode(["status"=>"error","message"=>"Permission denied."]); break; }
    $data = json_decode(file_get_contents("php://input"), true);
    // BUG FIX: was checking faculty fields (faculty_name, email…) instead of course fields
    if (
        empty($data['course_name'])  ||
        empty($data['course_code'])  ||
        empty($data['credits'])      ||
        empty($data['course_type'])  ||
        empty($data['dept_id'])      ||
        empty($data['semester_id'])
    ) {
        echo json_encode(["status" => "error", "message" => "Missing required course fields", "data" => $data]);
        exit;
    }
    $stmt = $conn->prepare("
        INSERT INTO Course (course_name, course_code, credits, course_type, dept_id, semester_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssisii",
        $data['course_name'], $data['course_code'], $data['credits'],
        $data['course_type'], $data['dept_id'], $data['semester_id']
    );
    echo $stmt->execute()
        ? json_encode(["status" => "success"])
        : json_encode(["status" => "error", "message" => $stmt->error]);
    break;

// ─── DEPARTMENTS ──────────────────────────────────────────
case "get_departments":
    $res = $conn->query("SELECT * FROM Department");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    break;

case "add_department":
    if (!$can_insert) { echo json_encode(["status"=>"error","message"=>"Permission denied."]); break; }
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data['dept_name'])) {
        echo json_encode(["status" => "error", "message" => "Department name required"]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO Department (dept_name) VALUES (?)");
    $stmt->bind_param("s", $data['dept_name']);
    echo $stmt->execute()
        ? json_encode(["status" => "success", "dept_id" => $conn->insert_id])
        : json_encode(["status" => "error", "message" => $stmt->error]);
    break;

// ─── SEMESTERS ────────────────────────────────────────────
case "get_semesters":
    $res = $conn->query("SELECT * FROM Semester");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    break;

case "add_semester":
    if (!$can_insert) { echo json_encode(["status"=>"error","message"=>"Permission denied."]); break; }
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data['semester_no']) || empty($data['academic_year'])) {
        echo json_encode(["status" => "error", "message" => "Semester number and academic year required"]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO Semester (semester_no, academic_year) VALUES (?, ?)");
    $stmt->bind_param("is", $data['semester_no'], $data['academic_year']);
    echo $stmt->execute()
        ? json_encode(["status" => "success", "semester_id" => $conn->insert_id])
        : json_encode(["status" => "error", "message" => $stmt->error]);
    break;

// ─── ALLOCATIONS ──────────────────────────────────────────
case "get_allocations":
    // BUG FIX: now also joins Department + Semester to get dept_name, semester_no, academic_year
    $res = $conn->query("
        SELECT a.*, f.faculty_name, c.course_name, c.course_code,
               d.dept_name, s.semester_no, s.academic_year
        FROM Allocation a
        JOIN Faculty    f ON a.faculty_id  = f.faculty_id
        JOIN Course     c ON a.course_id   = c.course_id
        JOIN Department d ON c.dept_id     = d.dept_id
        JOIN Semester   s ON a.semester_id = s.semester_id
    ");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    break;

case "add_allocation":
    if (!$can_insert) { echo json_encode(["status"=>"error","message"=>"Permission denied."]); break; }
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("
        INSERT INTO Allocation (faculty_id, course_id, semester_id, assigned_hours)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiii",
        $data['faculty_id'], $data['course_id'],
        $data['semester_id'], $data['assigned_hours']
    );
    echo $stmt->execute()
        ? json_encode(["status" => "success", "message" => "Allocation created successfully"])
        : json_encode(["status" => "error", "message" => $stmt->error]);
    break;

case "delete_allocation":
    if (!$can_delete) { echo json_encode(["status"=>"error","message"=>"Permission denied: only admin can delete."]); break; }
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $conn->prepare("DELETE FROM Allocation WHERE allocation_id = ?");
    $stmt->bind_param("i", $data['allocation_id']);
    echo $stmt->execute()
        ? json_encode(["status" => "success"])
        : json_encode(["status" => "error", "message" => $stmt->error]);
    break;

// ─── WORKLOAD ─────────────────────────────────────────────
case "get_workload":
    $res = $conn->query("
        SELECT f.faculty_name, d.dept_name,
               COALESCE(SUM(a.assigned_hours), 0) AS total_hours,
               f.max_workload
        FROM Faculty f
        JOIN Department d ON f.dept_id = d.dept_id
        LEFT JOIN Allocation a ON f.faculty_id = a.faculty_id
        GROUP BY f.faculty_id
    ");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    break;

// ─── SMART SUGGESTION (AI Agent) ──────────────────────────
case "get_smart_suggestion":
    $data      = json_decode(file_get_contents("php://input"), true);
    $course_id = intval($data['course_id']);

    $course      = $conn->query("SELECT dept_id FROM Course WHERE course_id = $course_id")->fetch_assoc();
    $course_dept = $course['dept_id'];

    $res = $conn->query("
        SELECT f.faculty_id, f.faculty_name, f.designation, f.max_workload, f.dept_id,
               COALESCE(SUM(a.assigned_hours), 0) AS total_hours
        FROM Faculty f
        LEFT JOIN Allocation a ON f.faculty_id = a.faculty_id
        GROUP BY f.faculty_id
    ");
    $faculties = $res->fetch_all(MYSQLI_ASSOC);

    foreach ($faculties as &$f) {
        $capacity   = $f['max_workload'] - $f['total_hours'];
        $dept_bonus = ($f['dept_id'] == $course_dept) ? 10 : 0;
        $desig_score = match ($f['designation']) {
            "Professor"           => 8,
            "Associate Professor" => 6,
            "Assistant Professor" => 4,
            default               => 2,
        };
        $f['score'] = $capacity + $dept_bonus + $desig_score;
    }

    usort($faculties, fn($a, $b) => $b['score'] <=> $a['score']);
    echo json_encode(array_slice($faculties, 0, 3));
    break;

}
?>