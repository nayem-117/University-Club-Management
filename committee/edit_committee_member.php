<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$error = "";
$success = "";

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) {
    header("Location: view_committee_members.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM club_executive_committee WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: view_committee_members.php");
    exit();
}

$member = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $term_start = $_POST['term_start'] ?? '';
    $term_end = $_POST['term_end'] ?? '';
    $photo = $_FILES['photo'] ?? null;

    if (!$name) {
        $error = "Name is required.";
    } elseif (!$position) {
        $error = "Position is required.";
    } elseif (!$term_start) {
        $error = "Term start date is required.";
    } elseif (!$term_end) {
        $error = "Term end date is required.";
    } elseif ($photo && $photo['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($photo['type'], $allowed_types)) {
            $error = "Photo must be JPG or PNG.";
        }
    }

    if (!$error) {
        $uploadDir = "../public/uploads/committee_photos/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $photoName = $member['photo'];
        if ($photo && $photo['error'] == 0) {
            $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $newPhotoName = uniqid("committee_") . ".$ext";
            if (move_uploaded_file($photo['tmp_name'], $uploadDir . $newPhotoName)) {
                // Delete old photo if exists
                if ($photoName && file_exists($uploadDir . $photoName)) {
                    unlink($uploadDir . $photoName);
                }
                $photoName = $newPhotoName;
            }
        }

        $stmt = $conn->prepare("UPDATE club_executive_committee SET name=?, position=?, term_start=?, term_end=?, photo=? WHERE student_id=?");
        $stmt->bind_param("ssssss", $name, $position, $term_start, $term_end, $photoName, $student_id);
        if ($stmt->execute()) {
            $success = "Committee member updated successfully.";
            // Refresh member data
            $stmt->close();
            $stmt = $conn->prepare("SELECT * FROM club_executive_committee WHERE student_id = ?");
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $member = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error = "Failed to update committee member.";
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit Committee Member - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-500 via-indigo-600 to-blue-600 min-h-screen flex items-center justify-center p-6">

<div class="bg-white shadow-xl rounded-lg w-full max-w-3xl p-8 ring-4 ring-indigo-300">

    <h1 class="text-4xl font-extrabold text-indigo-700 mb-8 text-center drop-shadow-lg">Edit Committee Member</h1>

    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded shadow"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded shadow"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6" novalidate>
        <div>
            <label class="block text-lg font-semibold text-gray-700 mb-2">Student ID (Primary Key)</label>
            <input type="text" value="<?= htmlspecialchars($member['student_id']) ?>" disabled class="w-full p-3 rounded border border-gray-300 bg-gray-100 cursor-not-allowed" />
        </div>

        <div>
            <label for="name" class="block text-lg font-semibold text-gray-700 mb-2">Full Name <span class="text-red-600">*</span></label>
            <input required type="text" name="name" id="name" value="<?= htmlspecialchars($member['name']) ?>" class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition" />
        </div>

        <div>
            <label for="position" class="block text-lg font-semibold text-gray-700 mb-2">Position <span class="text-red-600">*</span></label>
            <input required type="text" name="position" id="position" value="<?= htmlspecialchars($member['position']) ?>" class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition" />
        </div>

        <div>
            <label for="term_start" class="block text-lg font-semibold text-gray-700 mb-2">Term Start Date <span class="text-red-600">*</span></label>
            <input required type="date" name="term_start" id="term_start" value="<?= htmlspecialchars($member['term_start']) ?>" class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition" />
        </div>

        <div>
            <label for="term_end" class="block text-lg font-semibold text-gray-700 mb-2">Term End Date <span class="text-red-600">*</span></label>
            <input required type="date" name="term_end" id="term_end" value="<?= htmlspecialchars($member['term_end']) ?>" class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition" />
        </div>

        <div>
            <label for="photo" class="block text-lg font-semibold text-gray-700 mb-2">
                Photo (JPG or PNG) - Current:
                <?php if ($member['photo']): ?>
                    <img src="../public/uploads/committee_photos/<?= htmlspecialchars($member['photo']) ?>" alt="Photo" class="inline-block w-16 h-16 rounded object-cover ml-2" />
                <?php else: ?>
                    None
                <?php endif; ?>
            </label>
            <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png" class="w-full text-indigo-700" />
        </div>

        <div class="flex justify-between items-center">
            <button type="submit" name="submit" class="bg-indigo-600 text-white px-8 py-3 rounded shadow hover:bg-indigo-700 transition font-semibold tracking-wide">
                Update Member
            </button>
            <a href="view_committee_members.php" class="text-indigo-600 font-semibold hover:underline">Back to Committee List</a>
        </div>
    </form>
</div>
</body>
</html>
