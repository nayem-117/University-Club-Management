<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$event_id = $_GET['event_id'] ?? '';
if (!$event_id) {
    header("Location: view_events.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->close();

header("Location: view_events.php");
exit();
?>
