<?php
include_once "../includes/auth.php";
include_once "../config/db.php";

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $batch = trim($_POST['batch']);
    $status = isset($_POST['status']) && $_POST['status'] == 'on' ? 1 : 0;

    $profile_photo = $_FILES['profile_photo'] ?? null;

    // Basic validation
    if (!$student_id) {
        $error = "Student ID is required.";
    } elseif (!$name) {
        $error = "Name is required.";
    } elseif (!$department) {
        $error = "Department is required.";
    } elseif (!$batch) {
        $error = "Batch is required.";
    } elseif ($profile_photo && $profile_photo['error'] == 0) {
        $allowed_img_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($profile_photo['type'], $allowed_img_types)) {
            $error = "Profile photo must be JPG or PNG.";
        }
    }

    if (!$error) {
        // Check if student_id already exists (to prevent duplicate PK error)
        $stmtCheck = $conn->prepare("SELECT student_id FROM members WHERE student_id = ?");
        $stmtCheck->bind_param("s", $student_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows > 0) {
            $error = "Student ID already exists. Please use a unique Student ID.";
        }
        $stmtCheck->close();
    }

    if (!$error) {
        // Handle profile photo upload
        $profilePhotoName = null;
        if ($profile_photo && $profile_photo['error'] == 0) {
            $uploadDir = "../public/uploads/profile_photos/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($profile_photo['name'], PATHINFO_EXTENSION);
            $profilePhotoName = uniqid("profile_") . '.' . $ext;
            if (!move_uploaded_file($profile_photo['tmp_name'], $uploadDir . $profilePhotoName)) {
                $error = "Failed to upload profile photo.";
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO members (student_id, name, department, batch, status, profile_photo) VALUES (?, ?, ?, ?, ?, ?)");
        if(!$stmt){
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("sssiss", $student_id, $name, $department, $batch, $status, $profilePhotoName);
            if ($stmt->execute()) {
                $success = "Member added successfully.";
                // Clear fields to empty after success
                $student_id = $name = $department = $batch = "";
                $status = 0;
                $profilePhotoName = null;
            } else {
                // Handle DB execution error gracefully
                $error = "Failed to add member. Database error: " . $stmt->error;
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
<title>Add Member - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-500 via-indigo-600 to-blue-600 min-h-screen flex items-center justify-center p-6">

<div class="bg-white shadow-xl rounded-lg w-full max-w-3xl p-8 ring-4 ring-indigo-300">
    <h1 class="text-4xl font-extrabold text-indigo-700 mb-8 text-center drop-shadow-lg">Add New Member</h1>

    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded shadow"><?=htmlspecialchars($error)?></div>
    <?php elseif ($success): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded shadow"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6" novalidate>
        <div>
            <label for="student_id" class="block text-lg font-semibold text-gray-700 mb-2">Student ID <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="student_id"
                id="student_id"
                value="<?= isset($student_id) ? htmlspecialchars($student_id) : '' ?>"
                required
                placeholder="Unique Student ID"
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
                placeholder="Member's full name"
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="department" class="block text-lg font-semibold text-gray-700 mb-2">Department <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="department"
                id="department"
                value="<?= isset($department) ? htmlspecialchars($department) : '' ?>"
                required
                placeholder="Department or faculty"
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="batch" class="block text-lg font-semibold text-gray-700 mb-2">Batch <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="batch"
                id="batch"
                value="<?= isset($batch) ? htmlspecialchars($batch) : '' ?>"
                required
                placeholder="Batch or year"
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div class="flex items-center space-x-4">
            <input
                type="checkbox"
                name="status"
                id="status"
                class="h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                <?= isset($status) && $status ? "checked" : "" ?>
            />
            <label for="status" class="text-gray-700 font-medium select-none">Active Member</label>
        </div>

        <div>
            <label for="profile_photo" class="block text-lg font-semibold text-gray-700 mb-2">Profile Photo (JPG or PNG)</label>
            <input
                type="file"
                name="profile_photo"
                id="profile_photo"
                accept=".jpg,.jpeg,.png"
                class="w-full text-indigo-700"
            />
        </div>

        <div class="flex justify-between items-center">
            <button
                type="submit"
                name="submit"
                class="bg-indigo-600 text-white px-8 py-3 rounded shadow hover:bg-indigo-700 transition font-semibold tracking-wide"
            >
                Add Member
            </button>
            <a href="view_members.php" class="text-indigo-600 font-semibold hover:underline">Back to Members List</a>
        </div>
    </form>
</div>

</body>
</html>
