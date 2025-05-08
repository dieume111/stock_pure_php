<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding: 0;
        }

        .header {
            background-color: #333;
            padding: 15px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
        }

        .back-button {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        select, input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .attendance-table th, .attendance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .attendance-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .attendance-table tr:hover {
            background-color: #f5f5f5;
        }

        .submit-btn {
            background-color:rgb(40, 120, 167);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color:rgb(98, 33, 136);
        }

        .status-present {
            color:rgb(215, 43, 238);
            font-weight: bold;
        }

        .status-absent {
            color: #dc3545;
            font-weight: bold;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Attendance</h1>
        <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
    </div>

    <div class="container">
        <?php
        
        $conn = mysqli_connect("localhost", "root", "", "school_records");
        
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $date = mysqli_real_escape_string($conn, $_POST['date']);
            $courseid = mysqli_real_escape_string($conn, $_POST['course']);
            $success = true;
            $error_message = "";

            
            mysqli_begin_transaction($conn);

            try {
                foreach ($_POST['attendance'] as $studentid => $status) {
                    $studentid = mysqli_real_escape_string($conn, $studentid);
                    $status = mysqli_real_escape_string($conn, $status);

                    $sql = "INSERT INTO attendance (studentid, courseid, attendancedate, attendancestatus) 
                            VALUES ('$studentid', '$courseid', '$date', '$status')";

                    if (!mysqli_query($conn, $sql)) {
                        throw new Exception(mysqli_error($conn));
                    }
                }

                mysqli_commit($conn);
                echo '<div class="message success">Attendance recorded successfully!</div>';
            } catch (Exception $e) {
                mysqli_rollback($conn);
                echo '<div class="message error">Error: ' . $e->getMessage() . '</div>';
            }
        }

        // Get all courses
        $courses_result = mysqli_query($conn, "SELECT * FROM courses ORDER BY coursename");
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="course">Course:</label>
                <select id="course" name="course" required>
                    <option value="">Select Course</option>
                    <?php
                    while ($course = mysqli_fetch_assoc($courses_result)) {
                        echo "<option value='" . $course['courseid'] . "'>" . htmlspecialchars($course['coursename']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Attendance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    
                    $students_result = mysqli_query($conn, "SELECT * FROM students ORDER BY firstname, lastname");
                    
                    while ($student = mysqli_fetch_assoc($students_result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($student['studentid']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['firstname'] . " " . $student['lastname']) . "</td>";
                        echo "<td>
                                <select name='attendance[" . $student['studentid'] . "]' required>
                                    <option value='Present'>Present</option>
                                    <option value='Absent'>Absent</option>
                                </select>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div class="form-group" style="margin-top: 20px;">
                <button type="submit" class="submit-btn">Submit Attendance</button>
            </div>
        </form>
    </div>
</body>
</html>