<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$meeting_id = $_GET['meeting_id'] ?? '';
if (!$meeting_id || !ctype_digit($meeting_id)) {
    header("Location: view_meetings.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM meetings WHERE meeting_id = ?");
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$stmt->close();

header("Location: view_meetings.php");
exit();
?>
