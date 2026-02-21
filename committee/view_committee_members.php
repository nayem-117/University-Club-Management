<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$search = trim($_GET['search'] ?? '');

$whereClause = '';
$params = [];
$types = '';

if ($search) {
    $like = "%$search%";
    $whereClause = "WHERE student_id LIKE ? OR name LIKE ? OR position LIKE ?";
    $params = [$like, $like, $like];
    $types = "sss";
}

$sql = "SELECT * FROM club_executive_committee $whereClause ORDER BY term_start DESC";
$stmt = $conn->prepare($sql);
if ($search) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Committee Members - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
<div class="max-w-7xl mx-auto bg-white rounded shadow p-8">

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-indigo-700">Club Executive Committee</h1>
    <a href="add_committee_member.php" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700 transition">Add Member</a>
</div>

<form method="get" class="mb-6">
    <input 
        type="text" 
        name="search" 
        value="<?=htmlspecialchars($search)?>" 
        placeholder="Search by Student ID, name or position" 
        class="w-full p-2 border rounded border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
    />
</form>

<div class="overflow-x-auto">
<table class="min-w-full border border-gray-300 rounded">
<thead class="bg-indigo-600 text-white">
<tr>
    <th class="px-4 py-2 text-left">Student ID</th>
    <th class="px-4 py-2 text-left">Name</th>
    <th class="px-4 py-2 text-left">Position</th>
    <th class="px-4 py-2 text-left">Term Start</th>
    <th class="px-4 py-2 text-left">Term End</th>
    <th class="px-4 py-2 text-left">Photo</th>
    <th class="px-4 py-2 text-left">Actions</th>
</tr>
</thead>
<tbody class="bg-white">
<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr class="border-b">
        <td class="px-4 py-2 font-mono"><?= htmlspecialchars($row['student_id']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($row['position']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($row['term_start']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($row['term_end']) ?></td>
        <td class="px-4 py-2">
            <?php if ($row['photo']): ?>
                <img src="../public/uploads/committee_photos/<?= htmlspecialchars($row['photo']) ?>" alt="Photo" class="w-12 h-12 rounded object-cover"/>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
        <td class="px-4 py-2 space-x-2 text-sm">
            <a href="edit_committee_member.php?student_id=<?= urlencode($row['student_id']) ?>" class="text-blue-600 hover:underline">Edit</a>
            <a href="delete_committee_member.php?student_id=<?= urlencode($row['student_id']) ?>" onclick="return confirm('Are you sure you want to delete this member?');" class="text-red-600 hover:underline">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="7" class="text-center p-4">No committee members found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</body>
</html>
