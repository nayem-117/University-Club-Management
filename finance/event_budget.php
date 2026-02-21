<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$error = "";
$success = "";

// Fetch events for selection
$events = [];
$stmt = $conn->prepare("SELECT event_id, title, budget FROM events ORDER BY event_date DESC");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

$event_id = $_POST['event_id'] ?? '';
$budget = null;

if ($event_id) {
    // Fetch current budget of selected event (for showing in input)
    $stmt = $conn->prepare("SELECT budget FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($budget);
    $stmt->fetch();
    $stmt->close();
}

if (isset($_POST['submit'])) {
    $event_id = $_POST['event_id'] ?? '';
    $budget = $_POST['budget'] ?? '';

    if (!$event_id) {
        $error = "Please select an event.";
    } elseif (!is_numeric($budget) || floatval($budget) < 0) {
        $error = "Budget must be a non-negative number.";
    }

    // Check event existence
    if (!$error) {
        $stmtCheck = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
        $stmtCheck->bind_param("i", $event_id);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        if ($resCheck->num_rows === 0) {
            $error = "Selected event does not exist.";
        }
        $stmtCheck->close();
    }

    // Update budget
    if (!$error) {
        $stmtUpdate = $conn->prepare("UPDATE events SET budget = ?, updated_at = NOW() WHERE event_id = ?");
        $stmtUpdate->bind_param("di", $budget, $event_id);
        if ($stmtUpdate->execute()) {
            $success = "Budget updated successfully.";
        } else {
            $error = "Failed to update budget: " . $stmtUpdate->error;
        }
        $stmtUpdate->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Event Budget Management - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-yellow-400 via-orange-400 to-red-400 min-h-screen flex items-center justify-center p-6">
<div class="bg-white rounded shadow-xl w-full max-w-2xl p-8 ring-4 ring-orange-300">

    <h1 class="text-3xl font-bold text-orange-700 mb-6 text-center drop-shadow">Event Budget Management</h1>

    <?php if ($error): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded shadow"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded shadow"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6" novalidate>
        <div>
            <label for="event_id" class="block font-semibold mb-2 text-gray-700">Select Event <span class="text-red-600">*</span></label>
            <select name="event_id" id="event_id" required class="w-full p-3 border rounded border-gray-300 focus:ring-4 focus:ring-orange-400" onchange="this.form.submit()">
                <option value="">-- Select Event --</option>
                <?php foreach ($events as $ev): ?>
                    <option value="<?= htmlspecialchars($ev['event_id']) ?>" <?= ($ev['event_id'] == $event_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="text-sm text-gray-500 mt-1">Changing selection reloads budget value.</p>
        </div>

        <?php if ($event_id): ?>
        <div>
            <label for="budget" class="block font-semibold mb-2 text-gray-700">Budget Amount <span class="text-red-600">*</span></label>
            <input type="number" step="0.01" min="0" name="budget" id="budget" required value="<?= htmlspecialchars($budget ?? '') ?>"
            class="w-full p-3 border rounded border-gray-300 focus:ring-4 focus:ring-orange-400" />
        </div>
        <div class="flex justify-end">
            <button type="submit" name="submit" class="bg-orange-600 text-white px-8 py-3 rounded shadow hover:bg-orange-700 transition font-semibold tracking-wide">
                Save Budget
            </button>
        </div>
        <?php endif; ?>
    </form>

</div>
</body>
</html>
