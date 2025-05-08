<?php
session_start();

// Include database connection
include 'conn.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_attendance'])) {
    // Get all form data
    $firstnames = $_POST['firstname'] ?? [];
    $lastnames = $_POST['lastname'] ?? [];
    $genders = $_POST['gender'] ?? [];
    $dateofbirths = $_POST['dateofbirth'] ?? [];
    $contactnumbers = $_POST['contactnumber'] ?? [];
    $emails = $_POST['email'] ?? [];
    $addresses = $_POST['address'] ?? [];
    $dates = $_POST['date'] ?? [];

    // Prepare the insert statement
    $stmt = $conn->prepare("INSERT INTO students (firstname, lastname, gender, dateofbirth, contactnumber, email, address, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $successCount = 0;
        $errorCount = 0;

        // Loop through each row of data
        for ($i = 0; $i < count($firstnames); $i++) {
            // Skip empty rows
            if (empty($firstnames[$i]) && empty($lastnames[$i])) {
                continue;
            }

            // Basic validation
            if (empty($firstnames[$i]) || empty($lastnames[$i]) || empty($genders[$i]) || empty($dateofbirths[$i])) {
                $error = "Required fields are missing in row " . ($i + 1);
                continue;
            }

            // Clean and validate data
            $firstname = trim($firstnames[$i]);
            $lastname = trim($lastnames[$i]);
            $gender = $genders[$i];
            $dateofbirth = $dateofbirths[$i];
            $contactnumber = trim($contactnumbers[$i]);
            $email = trim($emails[$i]);
            $address = trim($addresses[$i]);
            $date = $dates[$i];

            // Validate email if provided
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format in row " . ($i + 1);
                continue;
            }

            // Bind parameters
            $stmt->bind_param("ssssssss", 
                $firstname,
                $lastname,
                $gender,
                $dateofbirth,
                $contactnumber,
                $email,
                $address,
                $date
            );

            // Execute the statement
            if ($stmt->execute()) {
                $successCount++;
            } else {
                $errorCount++;
                $error = "Error inserting data: " . $stmt->error;
            }
        }

        $stmt->close();

        if ($successCount > 0) {
            $success = "Successfully added $successCount student(s) to the database.";
        }
        if ($errorCount > 0) {
            $error = ($error ? $error . " " : "") . "Failed to add $errorCount student(s).";
        }
    } else {
        $error = "Database error: " . $conn->error;
    }
}

// Fetch existing students from database
$students = [];
$query = "SELECT * FROM students ORDER BY date DESC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $result->free();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information - School Records System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .data-entry-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-entry-table th, .data-entry-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .data-entry-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .data-entry-table input[type="text"],
        .data-entry-table input[type="email"],
        .data-entry-table input[type="tel"],
        .data-entry-table input[type="date"],
        .data-entry-table select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .add-button {
            background-color: #ffcccc;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .add-button:hover {
            background-color: #ff9999;
        }
        .add-student-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .add-student-row td {
            text-align: center;
        }
        .submit-section {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 20px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .container {
            padding-bottom: 100px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>School Attendance System</h2>
        <div class="nav-menu">
            <a href="dashboard.php" class="btn-info">Back to Home</a>
            <!-- <a href="students.php "class="btn-info">Students</a>
            <a href="courses.php">Courses</a>
            <a href="student_table.php">attendance</a> -->
            
            <a href="dashboard.php?logout" class="btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Student Information</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="" class="attendance-form">
                <table class="data-entry-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Attendance</th>
                            <th>Date</th>
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
                            <td><?php echo htmlspecialchars($student['address']); ?></td>
                            <td>
                                <select name="attendance[<?php echo $student['studentid']; ?>]" 
                                        class="attendance-select"
                                        onchange="updateAttendance(<?php echo $student['studentid']; ?>)">
                                    <option value="Present" <?php echo ($student['attendance'] == 'Present') ? 'selected' : ''; ?>>Present</option>
                                    <option value="Absent" <?php echo ($student['attendance'] == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                    <option value="Late" <?php echo ($student['attendance'] == 'Late') ? 'selected' : ''; ?>>Late</option>
                                </select>
                            </td>
                            <td><?php echo htmlspecialchars($student['date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <script>
        function addStudent() {
            
            const rows = document.querySelectorAll('.data-entry-table tbody tr:not(.add-student-row)');
            const lastRow = rows[rows.length - 1];
            const rowNumber = parseInt(lastRow.querySelector('td:first-child').textContent);
            
            
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${rowNumber + 1}</td>
                <td><input type="text" name="firstname[]" placeholder="Enter first name" required></td>
                <td><input type="text" name="lastname[]" placeholder="Enter last name" required></td>
                <td>
                    <select name="gender[]" required>
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </td>
                <td><input type="date" name="dateofbirth[]" required></td>
                <td><input type="tel" name="contactnumber[]" placeholder="Enter contact number"></td>
                <td><input type="email" name="email[]" placeholder="Enter email"></td>
                <td><input type="text" name="address[]" placeholder="Enter address"></td>
                <td><input type="date" name="date[]" value="<?php echo date('Y-m-d'); ?>" readonly></td>
            `;
            
            // Insert new row before the "Add a Student" row
            const addStudentRow = document.querySelector('.add-student-row');
            addStudentRow.parentNode.insertBefore(newRow, addStudentRow);
        }

        function updateAttendance(studentId) {
            const attendanceSelect = document.querySelector(`select[name="attendance[${studentId}]"]`);
            const attendance = attendanceSelect.value;
            
            // Show loading state
            attendanceSelect.disabled = true;
            
            fetch('update_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `student_id=${studentId}&attendance=${attendance}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message briefly
                    const originalColor = attendanceSelect.style.backgroundColor;
                    attendanceSelect.style.backgroundColor = '#d4edda';
                    setTimeout(() => {
                        attendanceSelect.style.backgroundColor = originalColor;
                    }, 1000);
                } else {
                    alert('Error updating attendance: ' + data.message);
                    // Reset to previous value if update failed
                    attendanceSelect.value = attendanceSelect.getAttribute('data-previous-value');
                }
            })
            .catch(error => {
                alert('Error updating attendance: ' + error);
                // Reset to previous value if update failed
                attendanceSelect.value = attendanceSelect.getAttribute('data-previous-value');
            })
            .finally(() => {
                attendanceSelect.disabled = false;
            });
        }

        // Add event listeners to all attendance selects when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            const attendanceSelects = document.querySelectorAll('.attendance-select');
            attendanceSelects.forEach(select => {
                // Store initial value
                select.setAttribute('data-previous-value', select.value);
            });
        });

        function deleteStudent(studentId) {
            if (confirm('Are you sure you want to delete this student?')) {
                window.location.href = 'delete_student.php?id=' + studentId;
            }
        }
    </script>
</body>
</html>