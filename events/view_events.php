<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$search = trim($_GET['search'] ?? '');

$whereClause = '';
$params = [];
$types = '';

if ($search) {
    $like = "%$search%";
    $whereClause = "WHERE e.title LIKE ? OR e.venue LIKE ?";
    $params = [$like, $like];
    $types = "ss";
}

$sql = "SELECT e.*, c.name AS coordinator_name FROM events e JOIN club_executive_committee c ON e.coordinator_id = c.student_id $whereClause ORDER BY e.event_date DESC, e.event_time DESC";
$stmt = $conn->prepare($sql);
if ($search) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Events - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-7xl mx-auto bg-white rounded shadow p-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-indigo-700">Events</h1>
        <a href="add_event.php" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700 transition">Add Event</a>
    </div>

    <form method="get" class="mb-6">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title or venue"
            class="w-full p-2 border rounded border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 rounded">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Event ID</th>
                    <th class="px-4 py-2 text-left">Title</th>
                    <th class="px-4 py-2 text-left">Coordinator</th>
                    <th class="px-4 py-2 text-left">Date & Time</th>
                    <th class="px-4 py-2 text-left">Venue</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                <?php if ($results->num_rows > 0): ?>
                    <?php while ($event = $results->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2 font-mono"><?= htmlspecialchars($event['event_id']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($event['title']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($event['coordinator_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($event['event_date'] . ' ' . $event['event_time']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($event['venue']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($event['status']) ?></td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="edit_event.php?event_id=<?= urlencode($event['event_id']) ?>" class="text-blue-600 hover:underline">Edit</a>
                                <a href="delete_event.php?event_id=<?= urlencode($event['event_id']) ?>" onclick="return confirm('Are you sure you want to delete this event?');" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="p-4 text-center">No events found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
