<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) {
    header("Location: view_committee_members.php");
    exit();
}

$stmt = $conn->prepare("SELECT photo FROM club_executive_committee WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: view_committee_members.php");
    exit();
}

$member = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM club_executive_committee WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$stmt->close();

$photoPath = "../public/uploads/committee_photos/" . $member['photo'];
if ($member['photo'] && file_exists($photoPath)) {
    unlink($photoPath);
}

header("Location: view_committee_members.php");
exit();
?>
