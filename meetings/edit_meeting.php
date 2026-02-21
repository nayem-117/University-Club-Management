<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$error = "";
$success = "";

$meeting_id = $_GET['meeting_id'] ?? '';
if (!$meeting_id || !ctype_digit($meeting_id)) {
    header("Location: view_meetings.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM meetings WHERE meeting_id = ?");
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: view_meetings.php");
    exit();
}

$meeting = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['submit'])) {
    $meeting_date = $_POST['meeting_date'] ?? "";
    $agenda = trim($_POST['agenda'] ?? "");
    $decisions = trim($_POST['decisions'] ?? "");

    if (!$meeting_date) {
        $error = "Meeting date is required.";
    } elseif (!$agenda) {
        $error = "Agenda is required.";
    } elseif (!$decisions) {
        $error = "Decisions are required.";
    }

    if (!$error) {
        $stmtUpdate = $conn->prepare("UPDATE meetings SET meeting_date = ?, agenda = ?, decisions = ?, updated_at = NOW() WHERE meeting_id = ?");
        $stmtUpdate->bind_param("sssi", $meeting_date, $agenda, $decisions, $meeting_id);
        if ($stmtUpdate->execute()) {
            $success = "Meeting updated successfully.";
            // Refresh data
            $stmtRefresh = $conn->prepare("SELECT * FROM meetings WHERE meeting_id = ?");
            $stmtRefresh->bind_param("i", $meeting_id);
            $stmtRefresh->execute();
            $resRefresh = $stmtRefresh->get_result();
            $meeting = $resRefresh->fetch_assoc();
            $stmtRefresh->close();
        } else {
            $error = "Failed to update meeting: " . $stmtUpdate->error;
        }
        $stmtUpdate->close();
    }
} else {
    $meeting_date = $meeting['meeting_date'];
    $agenda = $meeting['agenda'];
    $decisions = $meeting['decisions'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit Meeting - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-400 via-indigo-500 to-blue-600 min-h-screen flex items-center justify-center p-6">

<div class="bg-white rounded shadow-xl w-full max-w-3xl p-8 ring-4 ring-indigo-300">
    <h1 class="text-3xl font-extrabold text-indigo-700 mb-8 text-center drop-shadow-lg">Edit Meeting</h1>

    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded shadow"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded shadow"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate class="space-y-6">
        <div>
            <label for="meeting_date" class="block font-semibold text-gray-700 mb-2">Meeting Date <span class="text-red-600">*</span></label>
            <input 
                type="date"
                id="meeting_date"
                name="meeting_date"
                required
                value="<?= htmlspecialchars($meeting_date) ?>"
                class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
            />
        </div>

        <div>
            <label for="agenda" class="block font-semibold text-gray-700 mb-2">Agenda <span class="text-red-600">*</span></label>
            <textarea 
                id="agenda"
                name="agenda"
                rows="5"
                required
                class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
            ><?= htmlspecialchars($agenda) ?></textarea>
        </div>

        <div>
            <label for="decisions" class="block font-semibold text-gray-700 mb-2">Decisions <span class="text-red-600">*</span></label>
            <textarea 
                id="decisions"
                name="decisions"
                rows="5"
                required
                class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
            ><?= htmlspecialchars($decisions) ?></textarea>
        </div>

        <div class="flex justify-between items-center">
            <button type="submit" name="submit" class="bg-indigo-600 text-white px-8 py-3 rounded shadow hover:bg-indigo-700 transition font-semibold tracking-wide">
                Update Meeting
            </button>
            <a href="view_meetings.php" class="text-indigo-600 font-semibold hover:underline">Back to Meetings</a>
        </div>
    </form>
</div>
</body>
</html>
