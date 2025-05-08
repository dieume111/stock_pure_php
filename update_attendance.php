<?php
session_start();
include 'conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $attendance = $_POST['attendance'] ?? null;

    if (!$student_id || !$attendance) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    // Update attendance in the database
    $stmt = $conn->prepare("UPDATE students SET attendance = ? WHERE studentid = ?");
    if ($stmt) {
        $stmt->bind_param("si", $attendance, $student_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?> 