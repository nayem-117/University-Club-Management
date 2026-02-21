<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$meeting_id = $_GET['meeting_id'] ?? '';
if (!$meeting_id || !ctype_digit($meeting_id)) {
    header("Location: view_meetings.php");
    exit();
}

// Fetch meeting details
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

// Fetch all executive committee members
$exec_members = [];
$stmt = $conn->prepare("SELECT student_id, name, position FROM club_executive_committee ORDER BY name ASC");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $exec_members[] = $row;
}
$stmt->close();

// Fetch existing attendance for the meeting
$attendance = [];
$stmt = $conn->prepare("SELECT student_id, status FROM meeting_attendance WHERE meeting_id = ?");
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $attendance[$row['student_id']] = $row['status'];
}
$stmt->close();

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    $statuses = $_POST['status'] ?? [];

    $conn->begin_transaction();

    try {
        // Delete old attendance records
        $stmtDel = $conn->prepare("DELETE FROM meeting_attendance WHERE meeting_id = ?");
        $stmtDel->bind_param("i", $meeting_id);
        $stmtDel->execute();
        $stmtDel->close();

        $stmtInsert = $conn->prepare("INSERT INTO meeting_attendance (meeting_id, student_id, status) VALUES (?, ?, ?)");

        foreach ($exec_members as $member) {
            $student_id = $member['student_id'];
            $status_val = isset($statuses[$student_id]) && in_array($statuses[$student_id], ['Joined', 'Absent']) ? $statuses[$student_id] : 'Absent';
            $stmtInsert->bind_param("iss", $meeting_id, $student_id, $status_val);
            $stmtInsert->execute();
        }

        $stmtInsert->close();
        $conn->commit();

        $success = "Attendance updated successfully.";

        // Reload updated attendance
        $attendance = [];
        $stmt = $conn->prepare("SELECT student_id, status FROM meeting_attendance WHERE meeting_id = ?");
        $stmt->bind_param("i", $meeting_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $attendance[$row['student_id']] = $row['status'];
        }
        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to update attendance: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Attendance - Meeting on <?= htmlspecialchars($meeting['meeting_date']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-400 via-indigo-500 to-blue-600 min-h-screen p-6 flex flex-col items-center">

<div class="bg-white rounded shadow-xl w-full max-w-4xl p-8 ring-4 ring-indigo-300">

    <h1 class="text-3xl font-bold text-indigo-700 mb-6 text-center drop-shadow">Manage Attendance</h1>

    <p class="mb-6 text-center font-semibold">
        Meeting Date: <?= htmlspecialchars($meeting['meeting_date']) ?><br />
        Agenda: <?= nl2br(htmlspecialchars($meeting['agenda'])) ?>
    </p>

    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded shadow"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded shadow"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <table class="min-w-full border border-gray-300 rounded">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Executive Member</th>
                    <th class="px-4 py-2 text-left">Position</th>
                    <th class="px-4 py-2 text-center">Attendance Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exec_members as $member): 
                    $current_status = $attendance[$member['student_id']] ?? 'Absent';
                ?>
                <tr class="border-b">
                    <td class="px-4 py-2"><?= htmlspecialchars($member['name']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($member['position']) ?></td>
                    <td class="px-4 py-2 text-center">
                        <select name="status[<?= htmlspecialchars($member['student_id']) ?>]" class="p-2 border rounded border-gray-300 focus:ring-2 focus:ring-indigo-400">
                            <option value="Joined" <?= $current_status === 'Joined' ? 'selected' : '' ?>>Joined</option>
                            <option value="Absent" <?= $current_status === 'Absent' ? 'selected' : '' ?>>Absent</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-6 flex justify-between items-center">
            <button type="submit" name="submit" class="bg-indigo-600 text-white px-8 py-3 rounded shadow hover:bg-indigo-700 transition font-semibold tracking-wide">
                Save Attendance
            </button>
            <a href="view_meetings.php" class="text-indigo-600 font-semibold hover:underline">Back to Meetings</a>
        </div>
    </form>
</div>
</body>
</html>
