<?php
$conn = new mysqli("localhost", "root", "", "school_records");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>