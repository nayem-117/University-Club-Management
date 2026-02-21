<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$events = [];
$stmt = $conn->prepare("SELECT event_id, title FROM events ORDER BY event_date DESC");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

$filter_event_id = $_GET['event_id'] ?? '';
$filter_type = $_GET['type'] ?? '';
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';

$where = [];
$params = [];
$types = '';

if ($filter_event_id && ctype_digit($filter_event_id)) {
    $where[] = 'f.event_id = ?';
    $params[] = $filter_event_id;
    $types .= 'i';
}
if ($filter_type && in_array($filter_type, ['Income', 'Expense'])) {
    $where[] = 'f.type = ?';
    $params[] = $filter_type;
    $types .= 's';
}
if ($filter_start_date) {
    $where[] = 'f.transaction_date >= ?';
    $params[] = $filter_start_date;
    $types .= 's';
}
if ($filter_end_date) {
    $where[] = 'f.transaction_date <= ?';
    $params[] = $filter_end_date;
    $types .= 's';
}

$where_clause = '';
if ($where) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT f.*, e.title AS event_title 
        FROM finance f 
        JOIN events e ON f.event_id = e.event_id 
        $where_clause 
        ORDER BY f.transaction_date DESC, f.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $bind_names[] = $types;
    foreach ($params as $key => $val) {
        $bind_name = 'bind' . $key;
        $$bind_name = $val;
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
}
$stmt->execute();
$results = $stmt->get_result();

$total_income = 0.0;
$total_expense = 0.0;
$records = [];
while ($row = $results->fetch_assoc()) {
    if ($row['type'] === 'Income') {
        $total_income += (float)$row['amount'];
    } else {
        $total_expense += (float)$row['amount'];
    }
    $records[] = $row;
}

$balance = $total_income - $total_expense;

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Finance Records - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-6">
<div class="max-w-7xl mx-auto bg-white rounded shadow p-8">
    <h1 class="text-3xl font-bold text-green-700 mb-6">Finance Records</h1>

    <form method="get" class="grid grid-cols-4 gap-4 mb-6">
        <div>
            <label for="event_id" class="block font-semibold mb-1">Filter by Event</label>
            <select name="event_id" id="event_id" class="w-full p-2 border rounded border-gray-300">
                <option value="">-- All Events --</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?= htmlspecialchars($event['event_id']) ?>" <?= ($filter_event_id == $event['event_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($event['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="type" class="block font-semibold mb-1">Filter by Type</label>
            <select name="type" id="type" class="w-full p-2 border rounded border-gray-300">
                <option value="">-- All Types --</option>
                <option value="Income" <?= ($filter_type === 'Income') ? 'selected' : '' ?>>Income</option>
                <option value="Expense" <?= ($filter_type === 'Expense') ? 'selected' : '' ?>>Expense</option>
            </select>
        </div>
        <div>
            <label for="start_date" class="block font-semibold mb-1">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($filter_start_date) ?>" class="w-full p-2 border rounded border-gray-300" />
        </div>
        <div>
            <label for="end_date" class="block font-semibold mb-1">End Date</label>
            <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($filter_end_date) ?>" class="w-full p-2 border rounded border-gray-300" />
        </div>
        <div class="col-span-4 flex justify-between mt-4">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition font-semibold">Filter</button>
            <a href="add_finance.php" class="text-green-600 hover:underline font-semibold">Add New Record</a>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 rounded">
            <thead class="bg-green-600 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Event</th>
                    <th class="px-4 py-2 text-left">Type</th>
                    <th class="px-4 py-2 text-right">Amount</th>
                    <th class="px-4 py-2 text-left">Description</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                <?php if ($records): ?>
                    <?php foreach ($records as $rec): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?= htmlspecialchars($rec['transaction_date']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($rec['event_title']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($rec['type']) ?></td>
                            <td class="px-4 py-2 text-right <?= $rec['type'] == 'Expense' ? 'text-red-600' : 'text-green-600' ?>">
                                <?= number_format($rec['amount'], 2) ?>
                            </td>
                            <td class="px-4 py-2"><?= htmlspecialchars($rec['description']) ?></td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="edit_finance.php?finance_id=<?= $rec['finance_id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                                <a href="delete_finance.php?finance_id=<?= $rec['finance_id'] ?>" onclick="return confirm('Are you sure you want to delete this record?');" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="6" class="p-4 text-center text-gray-600">No finance records found.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="font-semibold bg-green-100">
                    <td colspan="3" class="px-4 py-2 text-right">Total Income:</td>
                    <td class="px-4 py-2 text-right text-green-700"><?= number_format($total_income, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
                <tr class="font-semibold bg-green-100">
                    <td colspan="3" class="px-4 py-2 text-right">Total Expense:</td>
                    <td class="px-4 py-2 text-right text-red-700">-<?= number_format($total_expense, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
                <tr class="font-bold bg-green-200">
                    <td colspan="3" class="px-4 py-2 text-right">Balance Remaining:</td>
                    <td class="px-4 py-2 text-right <?= $balance >= 0 ? 'text-green-800' : 'text-red-800' ?>"><?= number_format($balance, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>
