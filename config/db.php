<?php
// config/db.php
// Database connection file for University Club Management System

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_club_db"; // Change if your database name is different

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");
?>
