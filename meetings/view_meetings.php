<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$sql = "SELECT * FROM meetings ORDER BY meeting_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Meetings - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-7xl mx-auto bg-white rounded shadow p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-indigo-700">Meetings</h1>
        <a href="add_meeting.php" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700 transition">Add Meeting</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 rounded">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Agenda</th>
                    <th class="px-4 py-2 text-left">Decisions</th>
                    <th class="px-4 py-2 text-left">Attendance</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($row['meeting_date']) ?></td>
                            <td class="px-4 py-2 max-w-xs overflow-hidden text-ellipsis"><?= nl2br(htmlspecialchars($row['agenda'])) ?></td>
                            <td class="px-4 py-2 max-w-xs overflow-hidden text-ellipsis"><?= nl2br(htmlspecialchars($row['decisions'])) ?></td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <a href="meeting_attendance.php?meeting_id=<?= urlencode($row['meeting_id']) ?>" class="text-indigo-600 hover:underline">Manage Attendance</a>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap space-x-2">
                                <a href="edit_meeting.php?meeting_id=<?= urlencode($row['meeting_id']) ?>" class="text-green-600 hover:underline">Edit</a>
                                <a href="delete_meeting.php?meeting_id=<?= urlencode($row['meeting_id']) ?>" onclick="return confirm('Are you sure you want to delete this meeting?');" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="p-4 text-center text-gray-600">No meetings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
