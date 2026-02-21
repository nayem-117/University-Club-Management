<?php
// Enable error reporting (for development; disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../includes/auth.php";
include_once "../config/db.php";

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $term_start = $_POST['term_start'] ?? '';
    $term_end = $_POST['term_end'] ?? '';
    $photo = $_FILES['photo'] ?? null;

    // Validate required fields
    if (!$student_id) {
        $error = "Student ID is required.";
    } elseif (!$name) {
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

    // Check that student_id exists in members table (FK compliance)
    if (!$error) {
        $stmtCheck = $conn->prepare("SELECT student_id FROM members WHERE student_id = ?");
        $stmtCheck->bind_param("s", $student_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows === 0) {
            $error = "Student ID does not exist in Members. Please add as a member first.";
        }
        $stmtCheck->close();
    }

    // **NEW: Check if the student_id already exists in the executive committee to avoid duplicates**
    if (!$error) {
        $stmtDupCheck = $conn->prepare("SELECT student_id FROM club_executive_committee WHERE student_id = ?");
        $stmtDupCheck->bind_param("s", $student_id);
        $stmtDupCheck->execute();
        $resultDupCheck = $stmtDupCheck->get_result();
        if ($resultDupCheck->num_rows > 0) {
            $error = "This member is already present in the executive committee.";
        }
        $stmtDupCheck->close();
    }

    if (!$error) {
        $uploadDir = "../public/uploads/committee_photos/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $photoName = null;
        if ($photo && $photo['error'] == 0) {
            $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $photoName = uniqid("committee_") . ".$ext";
            if (!move_uploaded_file($photo['tmp_name'], $uploadDir . $photoName)) {
                $error = "Failed to upload photo.";
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO club_executive_committee (student_id, name, position, term_start, term_end, photo) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("ssssss", $student_id, $name, $position, $term_start, $term_end, $photoName);
            if ($stmt->execute()) {
                $success = "Committee member added successfully.";
                // Clear values after success to reset the form
                $student_id = $name = $position = $term_start = $term_end = "";
                $photoName = null;
            } else {
                $error = "Failed to add committee member. Possible duplicate or invalid Student ID.";
            }
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
<title>Add Committee Member - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-500 via-indigo-600 to-blue-600 min-h-screen flex items-center justify-center p-6">

<div class="bg-white shadow-xl rounded-lg w-full max-w-3xl p-8 ring-4 ring-indigo-300">

    <h1 class="text-4xl font-extrabold text-indigo-700 mb-8 text-center drop-shadow-lg">Add Committee Member</h1>

    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded shadow"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded shadow"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6" novalidate>
        <div>
            <label for="student_id" class="block text-lg font-semibold text-gray-700 mb-2">Student ID <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="student_id"
                id="student_id"
                value="<?= isset($student_id) ? htmlspecialchars($student_id) : '' ?>"
                required
                placeholder="Student ID (must exist in Members)"
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="name" class="block text-lg font-semibold text-gray-700 mb-2">Full Name <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="name"
                id="name"
                value="<?= isset($name) ? htmlspecialchars($name) : '' ?>"
                required
                placeholder="Full name"
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="position" class="block text-lg font-semibold text-gray-700 mb-2">Position <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="position"
                id="position"
                value="<?= isset($position) ? htmlspecialchars($position) : '' ?>"
                required
                placeholder="Position (e.g., President)"
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="term_start" class="block text-lg font-semibold text-gray-700 mb-2">Term Start Date <span class="text-red-600">*</span></label>
            <input
                type="date"
                name="term_start"
                id="term_start"
                value="<?= isset($term_start) ? htmlspecialchars($term_start) : '' ?>"
                required
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="term_end" class="block text-lg font-semibold text-gray-700 mb-2">Term End Date <span class="text-red-600">*</span></label>
            <input
                type="date"
                name="term_end"
                id="term_end"
                value="<?= isset($term_end) ? htmlspecialchars($term_end) : '' ?>"
                required
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="photo" class="block text-lg font-semibold text-gray-700 mb-2">Photo (JPG or PNG)</label>
            <input
                type="file"
                name="photo"
                id="photo"
                accept=".jpg,.jpeg,.png"
                class="w-full text-indigo-700"
            />
        </div>

        <div class="flex justify-between items-center">
            <button type="submit" name="submit" class="bg-indigo-600 text-white px-8 py-3 rounded shadow hover:bg-indigo-700 transition font-semibold tracking-wide">
                Add Committee Member
            </button>
            <a href="view_committee_members.php" class="text-indigo-600 font-semibold hover:underline">Back to Committee List</a>
        </div>
    </form>
</div>

</body>
</html>
