<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Management</title>
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
            margin: 30px auto;
            max-width: 1200px;
            padding: 0 20px;
        }

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .submit-btn {
            background-color:rgb(24, 81, 236);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color:rgb(33, 112, 136);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        tr:hover {
            background-color: #f5f5f5;
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
        <h1>Courses Management</h1>
        <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
    </div>

    <div class="container">
        <?php
        
        $conn = mysqli_connect("localhost", "root", "", "school_records");
        
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $coursename = mysqli_real_escape_string($conn, $_POST['coursename']);
            $course_descr = mysqli_real_escape_string($conn, $_POST['course_descr']);
            $duration = mysqli_real_escape_string($conn, $_POST['duration']);

            $sql = "INSERT INTO courses (coursename, course_descr, duration) 
                    VALUES ('$coursename', '$course_descr', '$duration')";

            if (mysqli_query($conn, $sql)) {
                echo '<div class="message success">Course added successfully!</div>';
            } else {
                echo '<div class="message error">Error: ' . mysqli_error($conn) . '</div>';
            }
        }
        ?>

        <div class="form-container">
            <h2>Add New Course</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="coursename">Course Name:</label>
                    <input type="text" id="coursename" name="coursename" required>
                </div>
                <div class="form-group">
                    <label for="course_descr">Course Description:</label>
                    <textarea id="course_descr" name="course_descr" required></textarea>
                </div>
                <div class="form-group">
                    <label for="duration">Duration:</label>
                    <input type="text" id="duration" name="duration" required>
                </div>
                <button type="submit" class="submit-btn">Add Course</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Available Courses</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course Name</th>
                        <th>Description</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = mysqli_query($conn, "SELECT * FROM courses ORDER BY courseid");
                    $counter = 1;
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['coursename']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['course_descr']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['duration']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>