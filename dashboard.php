<?php
session_start();


header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Clear any output buffers
    ob_clean();
    
    // Redirect to index.php
    header("Location: index.php");
    exit();
}


include 'conn.php';


$error = '';
$stats = [
    'students' => 0,
    'courses' => 0,
    'attendance' => 0,
    'grades' => 0
];

$tables = ['students', 'courses', 'attendance', 'grades'];
foreach ($tables as $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check->num_rows == 0) {
        $error = "Table '$table' does not exist. Please run the database setup script.";
        break;
    }
}

if (empty($error)) {
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM students");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['students'] = $row['total'];
        }
        $stmt->close();
    } else {
        $error = "Error preparing student count query: " . $conn->error;
    }

    
    if (empty($error)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM courses");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stats['courses'] = $row['total'];
            }
            $stmt->close();
        } else {
            $error = "Error preparing course count query: " . $conn->error;
        }
    }

    
    if (empty($error)) {
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE attendancedate = ?");
        if ($stmt) {
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stats['attendance'] = $row['total'];
            }
            $stmt->close();
        } else {
            $error = "Error preparing attendance count query: " . $conn->error;
        }
    }


    if (empty($error)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM grades");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stats['grades'] = $row['total'];
            }
            $stmt->close();
        } else {
            $error = "Error preparing grades count query: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - School Records System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <h2>School Records System</h2>
        <div class="nav-menu">
            <a href="dashboard.php" class="btn-info">Home</a>
            <a href="student_table.php">Students</a>
            <a href="courses.php">Courses</a>
            <a href="attendance.php">Attendance</a>
            <a href="grades.php">Grades</a>
            <a href="dashboard.php?logout" class="btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Welcome to School Records System</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
                <br>
                <a href="setup_database.php" class="btn btn-warning">Run Database Setup</a>
            </div>
        <?php endif; ?>
    
        <div class="dashboard">
            <div class="dashboard-card">
                <h3>Total Students</h3>
                <p class="stat-number"><?php echo $stats['students']; ?></p>
                <a href="students.php" class="btn btn-primary">Add Students</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Total Courses</h3>
                <p class="stat-number"><?php echo $stats['courses']; ?></p>
                <a href="courses.php" class="btn btn-primary">Manage Courses</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Today's Attendance</h3>
                <p class="stat-number"><?php echo $stats['attendance']; ?></p>
                <a href="attendance.php" class="btn btn-primary">View Attendance</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Grades Recorded</h3>
                <p class="stat-number"><?php echo $stats['grades']; ?></p>
                <a href="grades.php" class="btn btn-primary">Add Grades</a>
            </div>
        </div>

        
        </div>

        </div>
</body>
</html>
