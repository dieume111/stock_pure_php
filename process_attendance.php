<?php
session_start();
include 'conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $recorded_by = $_SESSION['user_id'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, student_name, date, status, recorded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $student_id, $student_name, $date, $status, $recorded_by);

    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
        header("Location: index.php?error=" . urlencode($error));
        exit();
    }

    $stmt->close();
}

$conn->close();
?> 