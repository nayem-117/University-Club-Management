<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$error = "";
$success = "";

$events = [];
$stmt = $conn->prepare("SELECT event_id, title FROM events ORDER BY event_date DESC");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

if (isset($_POST['submit'])) {
    $event_id = $_POST['event_id'] ?? '';
    $type = $_POST['type'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $transaction_date = $_POST['transaction_date'] ?? '';

    if (!$event_id) {
        $error = "Please select an event.";
    } elseif (!in_array($type, ['Income', 'Expense'])) {
        $error = "Invalid type selected.";
    } elseif (!is_numeric($amount) || floatval($amount) <= 0) {
        $error = "Amount must be a positive number.";
    } elseif (!$description) {
        $error = "Description is required.";
    } elseif (!$transaction_date) {
        $error = "Transaction date is required.";
    }

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

    if (!$error) {
        $stmtInsert = $conn->prepare("INSERT INTO finance (event_id, type, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->bind_param("isdss", $event_id, $type, $amount, $description, $transaction_date);
        if ($stmtInsert->execute()) {
            $success = "{$type} record added successfully.";
            $event_id = $type = $amount = $description = $transaction_date = "";
        } else {
            $error = "Failed to add finance record: " . $stmtInsert->error;
        }
        $stmtInsert->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Finance Record - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-green-400 via-teal-500 to-cyan-600 min-h-screen flex items-center justify-center p-6">
<div class="bg-white rounded shadow-xl w-full max-w-3xl p-8 ring-4 ring-teal-300">

    <h1 class="text-3xl font-bold text-teal-700 mb-6 text-center drop-shadow">Add Income / Expense</h1>

    <?php if ($error): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded shadow"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded shadow"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6" novalidate>
        <div>
            <label for="event_id" class="block font-semibold mb-2 text-gray-700">Event <span class="text-red-600">*</span></label>
            <select name="event_id" id="event_id" required class="w-full p-3 border rounded border-gray-300 focus:ring-4 focus:ring-teal-400">
                <option value="">-- Select Event --</option>
                <?php foreach ($events as $ev): ?>
                    <option value="<?= htmlspecialchars($ev['event_id']) ?>"
                        <?= (isset($event_id) && $event_id == $ev['event_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="type" class="block font-semibold mb-2 text-gray-700">Type <span class="text-red-600">*</span></label>
            <select name="type" id="type" required class="w-full p-3 border rounded border-gray-300 focus:ring-4 focus:ring-teal-400">
                <option value="">-- Select Type --</option>
                <option value="Income" <?= (isset($type) && $type == 'Income') ? 'selected' : '' ?>>Income</option>
                <option value="Expense" <?= (isset($type) && $type == 'Expense') ? 'selected' : '' ?>>Expense</option>
            </select>
        </div>
        <div>
            <label for="amount" class="block font-semibold mb-2 text-gray-700">Amount <span class="text-red-600">*</span></label>
            <input type="number" step="0.01" min="0" name="amount" id="amount" required
                value="<?= htmlspecialchars($amount ?? '') ?>"
                class="w-full p-3 border rounded border-gray-300 focus:ring-4 focus:ring-teal-400" />
        </div>
        <div>
            <label for="description" class="block font-semibold mb-2 text-gray-700">Description <span class="text-red-600">*</span></label>
            <input type="text" name="description" id="description" required
                value="<?= htmlspecialchars($description ?? '') ?>"
                placeholder="Description" class="w-full p-3 border rounded border-gray-300 focus:ring-4 focus:ring-teal-400" />
        </div>
        <div>
            <label for="transaction_date" class="block font-semibold mb-2 text-gray-700">Transaction Date <span class="text-red-600">*</span></label>
            <input type="date" name="transaction_date" id="transaction_date" required
                value="<?= htmlspecialchars($transaction_date ?? '') ?>"
                class="w-full p-3 border rounded border-gray-300 focus:ring-4 focus:ring-teal-400" />
        </div>
        <div class="flex justify-between items-center">
            <button type="submit" name="submit" class="bg-teal-600 text-white px-8 py-3 rounded shadow hover:bg-teal-700 transition font-semibold tracking-wide">
                Add Record
            </button>
            <a href="view_finance.php" class="text-teal-600 font-semibold hover:underline">View Finance Records</a>
        </div>
    </form>
</div>
</body>
</html>
