<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$finance_id = $_GET['finance_id'] ?? '';
if (!$finance_id || !ctype_digit($finance_id)) {
    header("Location: view_finance.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM finance WHERE finance_id = ?");
$stmt->bind_param("i", $finance_id);
$stmt->execute();
$stmt->close();

header("Location: view_finance.php");
exit();
