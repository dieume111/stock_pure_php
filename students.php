<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Set security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'conn.php';

// Initialize variables
$error = '';
$success = '';
$students = [];

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new student
                $firstname = trim($_POST['firstname']);
                $lastname = trim($_POST['lastname']);
                $gender = trim($_POST['gender']);
                $dateofbirth = trim($_POST['dateofbirth']);
                $contactnumber = trim($_POST['contactnumber']);
                $email = trim($_POST['email']);
                $address = trim($_POST['address']);
                
                if (empty($firstname) || empty($lastname) || empty($gender) || empty($dateofbirth)) {
                    $error = "Required fields are missing";
                } else {
                    $stmt = $conn->prepare("INSERT INTO students (firstname, lastname, gender, dateofbirth, contactnumber, email, address, date) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())");
                    $stmt->bind_param("sssssss", $firstname, $lastname, $gender, $dateofbirth, $contactnumber, $email, $address);
                    
                    if ($stmt->execute()) {
                        $success = "Student added successfully";
                    } else {
                        $error = "Error adding student: " . $stmt->error;
                    }
                    $stmt->close();
                }
                break;
                
            case 'delete':
                // Delete student
                if (isset($_POST['student_id'])) {
                    $student_id = (int)$_POST['student_id'];
                    $stmt = $conn->prepare("DELETE FROM students WHERE studentid = ?");
                    $stmt->bind_param("i", $student_id);
                    
                    if ($stmt->execute()) {
                        $success = "Student deleted successfully";
                    } else {
                        $error = "Error deleting student: " . $stmt->error;
                    }
                    $stmt->close();
                }
                break;
        }
    }
}

// Fetch all students
$stmt = $conn->prepare("SELECT * FROM students ORDER BY firstname ASC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - School Records System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <h2>School Records System</h2>
        <div class="nav-menu">
            <a href="dashboard.php">Back to Dashboard</a>
            <a href="students.php" class="active">Students</a>
            
            
           
            <a href="dashboard.php?logout" class="btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Manage Students</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Add New Student</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="firstname">First Name*</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name*</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender*</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dateofbirth">Date of Birth*</label>
                    <input type="date" id="dateofbirth" name="dateofbirth" required>
                </div>
                <div class="form-group">
                    <label for="contactnumber">Contact Number</label>
                    <input type="tel" id="contactnumber" name="contactnumber">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Student</button>
            </form>
        </div>

        <div class="card">
            <h2>Student List</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['studentid']); ?></td>
                        <td><?php echo htmlspecialchars($student['firstname']); ?></td>
                        <td><?php echo htmlspecialchars($student['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($student['gender']); ?></td>
                        <td><?php echo htmlspecialchars($student['dateofbirth']); ?></td>
                        <td><?php echo htmlspecialchars($student['contactnumber']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="student_id" value="<?php echo $student['studentid']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 