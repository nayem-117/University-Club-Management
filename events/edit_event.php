<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$error = "";
$success = "";

$event_id = $_GET['event_id'] ?? '';
if (!$event_id) {
    header("Location: view_events.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: view_events.php");
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

$committeeMembers = [];
$stmtCommittee = $conn->prepare("SELECT student_id, name, position FROM club_executive_committee ORDER BY name ASC");
$stmtCommittee->execute();
$resultCommittee = $stmtCommittee->get_result();
while ($row = $resultCommittee->fetch_assoc()) {
    $committeeMembers[] = $row;
}
$stmtCommittee->close();

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'] ?? "";
    $event_time = $_POST['event_time'] ?? "";
    $venue = trim($_POST['venue']);
    $coordinator_id = $_POST['coordinator_id'] ?? "";
    $status = $_POST['status'] ?? "";

    if (!$title) {
        $error = "Event title is required.";
    } elseif (!$event_date) {
        $error = "Event date is required.";
    } elseif (!$event_time) {
        $error = "Event time is required.";
    } elseif (!$venue) {
        $error = "Venue is required.";
    } elseif (!$coordinator_id) {
        $error = "Please select an event coordinator.";
    } elseif (!in_array($status, ['Scheduled', 'Completed', 'Cancelled'])) {
        $error = "Invalid event status.";
    }

    if (!$error) {
        $stmtCheck = $conn->prepare("SELECT student_id FROM club_executive_committee WHERE student_id = ?");
        $stmtCheck->bind_param("s", $coordinator_id);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        if ($resCheck->num_rows === 0) {
            $error = "Selected coordinator does not exist in executive committee.";
        }
        $stmtCheck->close();
    }

    if (!$error) {
        $stmtUpdate = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, venue=?, coordinator_id=?, status=? WHERE event_id=?");
        $stmtUpdate->bind_param("sssssssi", $title, $description, $event_date, $event_time, $venue, $coordinator_id, $status, $event_id);
        if ($stmtUpdate->execute()) {
            $success = "Event updated successfully.";
            // Refresh event data after update
            $stmtUpdate->close();
            $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $event = $res->fetch_assoc();
            $stmt->close();
        } else {
            $error = "Failed to update event: " . $stmtUpdate->error;
        }
    }
} else {
    $title = $event['title'];
    $description = $event['description'];
    $event_date = $event['event_date'];
    $event_time = $event['event_time'];
    $venue = $event['venue'];
    $coordinator_id = $event['coordinator_id'];
    $status = $event['status'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit Event - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-400 via-indigo-500 to-blue-600 min-h-screen flex items-center justify-center p-6">

<div class="bg-white rounded shadow-xl w-full max-w-4xl p-8 ring-4 ring-indigo-300">

<h1 class="text-4xl font-extrabold text-indigo-700 mb-8 text-center drop-shadow-lg">Edit Event</h1>

<?php if ($error): ?>
    <div class="mb-6 p-4 bg-red-100 text-red-700 rounded shadow"><?= htmlspecialchars($error) ?></div>
<?php elseif ($success): ?>
    <div class="mb-6 p-4 bg-green-100 text-green-700 rounded shadow"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" class="space-y-6" novalidate>
    <div>
        <label for="title" class="block font-semibold text-gray-700 mb-2">Title <span class="text-red-600">*</span></label>
        <input
            type="text"
            name="title"
            id="title"
            value="<?= htmlspecialchars($title) ?>"
            required
            placeholder="Event title"
            class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
        />
    </div>

    <div>
        <label for="description" class="block font-semibold text-gray-700 mb-2">Description</label>
        <textarea
            name="description"
            id="description"
            rows="4"
            class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
            placeholder="Event description (optional)"
        ><?= htmlspecialchars($description) ?></textarea>
    </div>

    <div class="grid grid-cols-2 gap-6">
        <div>
            <label for="event_date" class="block font-semibold text-gray-700 mb-2">Event Date <span class="text-red-600">*</span></label>
            <input
                type="date"
                name="event_date"
                id="event_date"
                value="<?= htmlspecialchars($event_date) ?>"
                required
                class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
            />
        </div>

        <div>
            <label for="event_time" class="block font-semibold text-gray-700 mb-2">Event Time <span class="text-red-600">*</span></label>
            <input
                type="time"
                name="event_time"
                id="event_time"
                value="<?= htmlspecialchars($event_time) ?>"
                required
                class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
            />
        </div>
    </div>

    <div>
        <label for="venue" class="block font-semibold text-gray-700 mb-2">Venue <span class="text-red-600">*</span></label>
        <input
            type="text"
            name="venue"
            id="venue"
            value="<?= htmlspecialchars($venue) ?>"
            required
            placeholder="Event venue/location"
            class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
        />
    </div>

    <div>
        <label for="coordinator_id" class="block font-semibold text-gray-700 mb-2">Event Coordinator <span class="text-red-600">*</span></label>
        <select
            name="coordinator_id"
            id="coordinator_id"
            required
            class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
        >
            <option value="">-- Select Coordinator --</option>
            <?php foreach ($committeeMembers as $member): ?>
            <option value="<?= htmlspecialchars($member['student_id']) ?>"
                <?= $coordinator_id === $member['student_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars("{$member['name']} ({$member['position']})") ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="status" class="block font-semibold text-gray-700 mb-2">Status</label>
        <select
            name="status"
            id="status"
            class="w-full p-3 border rounded focus:outline-none focus:ring-4 focus:ring-indigo-400"
        >
            <?php
            $statuses = ['Scheduled', 'Completed', 'Cancelled'];
            foreach ($statuses as $st):
            ?>
            <option value="<?= $st ?>" <?= $st === $status ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="flex justify-between items-center">
        <button type="submit" name="submit" class="bg-indigo-600 text-white px-8 py-3 rounded shadow hover:bg-indigo-700 transition font-semibold tracking-wide">
            Update Event
        </button>
        <a href="view_events.php" class="text-indigo-600 font-semibold hover:underline">Back to Events List</a>
    </div>
</form>
</div>
</body>
</html>
