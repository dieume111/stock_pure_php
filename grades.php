<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_records";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students from database
$sql_students = "SELECT * FROM students";
$result_students = $conn->query($sql_students);

// Fetch courses from database
$sql_courses = "SELECT * FROM courses";
$result_courses = $conn->query($sql_courses);

// Fetch recent grades with student and course names
$sql_grades = "SELECT g.*, CONCAT(s.firstname, ' ', s.lastname) as student_name, c.coursename 
               FROM grades g 
               JOIN students s ON g.studentid = s.studentid 
               JOIN courses c ON g.courseid = c.courseid 
               ORDER BY g.examdate DESC LIMIT 10";
$result_grades = $conn->query($sql_grades);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grades</title>
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

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .grades-table th, .grades-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .grades-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .grades-table tr:hover {
            background-color: #f5f5f5;
        }

        .submit-btn {
            background-color:rgb(40, 116, 167);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #218838;
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
        <h1>Student Grades</h1>
        <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
    </div>

    <div class="container">
        <?php
        // Handle form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $examdate = mysqli_real_escape_string($conn, $_POST['examdate']);
            $courseid = mysqli_real_escape_string($conn, $_POST['course']);
            $success = true;
            $error_message = "";

            // Begin transaction
            mysqli_begin_transaction($conn);

            try {
                foreach ($_POST['grades'] as $studentid => $grade) {
                    $studentid = mysqli_real_escape_string($conn, $studentid);
                    $grade = mysqli_real_escape_string($conn, $grade);

                    // Check if grade already exists for this student, course, and date
                    $check_sql = "SELECT * FROM grades WHERE studentid = '$studentid' AND courseid = '$courseid' AND examdate = '$examdate'";
                    $check_result = mysqli_query($conn, $check_sql);
                    
                    if (mysqli_num_rows($check_result) > 0) {
                        // Update existing grade
                        $sql = "UPDATE grades SET grade = '$grade' 
                                WHERE studentid = '$studentid' AND courseid = '$courseid' AND examdate = '$examdate'";
                    } else {
                        // Insert new grade
                        $sql = "INSERT INTO grades (studentid, courseid, examdate, grade) 
                                VALUES ('$studentid', '$courseid', '$examdate', '$grade')";
                    }

                    if (!mysqli_query($conn, $sql)) {
                        throw new Exception(mysqli_error($conn));
                    }
                }

                mysqli_commit($conn);
                echo '<div class="message success">Grades recorded successfully!</div>';
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
                <label for="examdate">Exam Date:</label>
                <input type="date" id="examdate" name="examdate" required value="<?php echo date('Y-m-d'); ?>">
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

            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Grade (0-100)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get all students
                    $students_result = mysqli_query($conn, "SELECT * FROM students ORDER BY firstname, lastname");
                    
                    while ($student = mysqli_fetch_assoc($students_result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($student['studentid']) . "</td>";
                        echo "<td>" . htmlspecialchars($student['firstname'] . " " . $student['lastname']) . "</td>";
                        echo "<td>
                                <input type='number' name='grades[" . $student['studentid'] . "]' min='0' max='100' step='0.01' required>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div class="form-group" style="margin-top: 20px;">
                <button type="submit" class="submit-btn">Submit Grades</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php
$conn->close();
?>
