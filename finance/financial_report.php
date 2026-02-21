<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? ''; // optional month filter

// Validate year and month
if (!preg_match('/^\d{4}$/', $year)) {
    $year = date('Y');
}
if ($month && (!preg_match('/^\d{1,2}$/', $month) || $month < 1 || $month > 12)) {
    $month = '';
}

$where_date = "YEAR(f.transaction_date) = ?";
$params = [$year];
$types = "i";

if ($month) {
    $where_date .= " AND MONTH(f.transaction_date) = ?";
    $params[] = $month;
    $types .= "i";
}

// Query summary aggregation grouped by event
$sql = "SELECT e.event_id, e.title, e.budget,
        SUM(CASE WHEN f.type = 'Income' THEN f.amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN f.type = 'Expense' THEN f.amount ELSE 0 END) AS total_expense
        FROM events e
        LEFT JOIN finance f ON e.event_id = f.event_id AND $where_date
        GROUP BY e.event_id, e.title, e.budget
        ORDER BY e.event_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

$total_budget = 0;
$total_income = 0;
$total_expense = 0;
$total_balance = 0;
foreach ($rows as $r) {
    $total_budget += $r['budget'];
    $total_income += $r['total_income'] ?? 0;
    $total_expense += $r['total_expense'] ?? 0;
}
$total_balance = $total_income - $total_expense;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Financial Report - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6 min-h-screen">

<div class="max-w-7xl mx-auto bg-white rounded shadow p-8">

    <h1 class="text-3xl font-bold text-indigo-700 mb-6">Financial Report for <?= htmlspecialchars($month ? "$year-$month" : $year) ?></h1>

    <form method="get" class="flex space-x-4 mb-6">

        <div>
            <label for="year" class="block font-semibold mb-1">Year</label>
            <input type="number" min="2000" max="<?= date('Y') + 10 ?>" id="year" name="year" value="<?= htmlspecialchars($year) ?>"
                class="p-2 border rounded border-gray-300" required />
        </div>

        <div>
            <label for="month" class="block font-semibold mb-1">Month (optional)</label>
            <select id="month" name="month" class="p-2 border rounded border-gray-300">
                <option value="">-- All Months --</option>
                <?php foreach (range(1,12) as $m): ?>
                    <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>><?= DateTime::createFromFormat('!m',$m)->format('F') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="self-end">
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700 transition font-semibold">Generate Report</button>
        </div>
    </form>

    <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 rounded">
        <thead class="bg-indigo-600 text-white">
            <tr>
                <th class="px-4 py-2 text-left">Event</th>
                <th class="px-4 py-2 text-right">Budget</th>
                <th class="px-4 py-2 text-right">Income</th>
                <th class="px-4 py-2 text-right">Expense</th>
                <th class="px-4 py-2 text-right">Balance</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <?php if ($rows): ?>
                <?php foreach ($rows as $r): 
                    $balance = ($r['total_income'] ?? 0) - ($r['total_expense'] ?? 0);
                ?>
                <tr class="border-b">
                    <td class="px-4 py-2"><?= htmlspecialchars($r['title']) ?></td>
                    <td class="px-4 py-2 text-right"><?= number_format($r['budget'], 2) ?></td>
                    <td class="px-4 py-2 text-right text-green-700"><?= number_format($r['total_income'] ?? 0, 2) ?></td>
                    <td class="px-4 py-2 text-right text-red-700"><?= number_format($r['total_expense'] ?? 0, 2) ?></td>
                    <td class="px-4 py-2 text-right <?= $balance >= 0 ? 'text-green-800' : 'text-red-800' ?>"><?= number_format($balance, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="p-4 text-center text-gray-600">No data found for selected period.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot class="font-bold bg-indigo-100">
            <tr>
                <td class="px-4 py-2 text-right">Totals:</td>
                <td class="px-4 py-2 text-right"><?= number_format($total_budget, 2) ?></td>
                <td class="px-4 py-2 text-right text-green-700"><?= number_format($total_income, 2) ?></td>
                <td class="px-4 py-2 text-right text-red-700"><?= number_format($total_expense, 2) ?></td>
                <td class="px-4 py-2 text-right <?= $total_balance >= 0 ? 'text-green-800' : 'text-red-800' ?>"><?= number_format($total_balance, 2) ?></td>
            </tr>
        </tfoot>
    </table>
    </div>

    <div class="mt-6">
        <a href="view_finance.php" class="text-indigo-600 hover:underline font-semibold">Back to Finance Records</a>
    </div>
</div>
</body>
</html>
