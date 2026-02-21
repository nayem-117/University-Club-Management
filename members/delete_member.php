<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) {
    header("Location: view_members.php");
    exit();
}

// Get profile photo filename before deleting
$stmt = $conn->prepare("SELECT profile_photo FROM members WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: view_members.php");
    exit();
}

$member = $result->fetch_assoc();
$stmt->close();

// Delete member record
$stmt = $conn->prepare("DELETE FROM members WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$stmt->close();

// Delete profile photo file if exists
$photoPath = "../public/uploads/profile_photos/" . $member['profile_photo'];
if ($member['profile_photo'] && file_exists($photoPath)) {
    unlink($photoPath);
}

header("Location: view_members.php");
exit();
?>
