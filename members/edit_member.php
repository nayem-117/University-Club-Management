<?php 
include_once "../includes/auth.php";
include_once "../config/db.php";

$error = "";
$success = "";

// Get student_id from GET parameter
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) {
    header("Location: view_members.php");
    exit();
}

// Fetch existing member data
$stmt = $conn->prepare("SELECT * FROM members WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: view_members.php");
    exit();
}

$member = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $batch = trim($_POST['batch']);
    $status = isset($_POST['status']) ? 1 : 0;
    $profile_photo = $_FILES['profile_photo'] ?? null;

    if (!$name) {
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
        $uploadDir = "../public/uploads/profile_photos/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $profilePhotoName = $member['profile_photo']; // Keep old photo by default
        
        if ($profile_photo && $profile_photo['error'] == 0) {
            $ext = pathinfo($profile_photo['name'], PATHINFO_EXTENSION);
            $newPhotoName = uniqid("profile_") . ".$ext";
            $uploadFilePath = $uploadDir . $newPhotoName;
            if (move_uploaded_file($profile_photo['tmp_name'], $uploadFilePath)) {
                // Delete old photo
                if ($profilePhotoName && file_exists($uploadDir . $profilePhotoName)) {
                    unlink($uploadDir . $profilePhotoName);
                }
                $profilePhotoName = $newPhotoName;
            } else {
                $error = "Failed to upload new profile photo.";
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE members SET name=?, department=?, batch=?, status=?, profile_photo=? WHERE student_id=?");
        $stmt->bind_param("sssiss", $name, $department, $batch, $status, $profilePhotoName, $student_id);

        if ($stmt->execute()) {
            $success = "Member updated successfully.";

            // Refresh data after update
            $stmt->close();
            $stmt = $conn->prepare("SELECT * FROM members WHERE student_id = ?");
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $member = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error = "Failed to update member.";
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
<title>Edit Member - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-500 via-indigo-600 to-blue-600 min-h-screen flex items-center justify-center p-6">

<div class="bg-white shadow-xl rounded-lg w-full max-w-3xl p-8 ring-4 ring-indigo-300">

    <h1 class="text-4xl font-extrabold text-indigo-700 mb-8 text-center drop-shadow-lg">Edit Member</h1>

    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded shadow"><?=htmlspecialchars($error)?></div>
    <?php elseif ($success): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded shadow"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6" novalidate>
        <div>
            <label class="block text-lg font-semibold text-gray-700 mb-2">Student ID (Primary Key)</label>
            <input
                type="text"
                value="<?= htmlspecialchars($member['student_id']) ?>"
                disabled
                class="w-full p-3 rounded border border-gray-300 bg-gray-100 cursor-not-allowed"
            />
        </div>

        <div>
            <label for="name" class="block text-lg font-semibold text-gray-700 mb-2">Full Name <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="name"
                id="name"
                value="<?= htmlspecialchars($member['name']) ?>"
                required
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="department" class="block text-lg font-semibold text-gray-700 mb-2">Department <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="department"
                id="department"
                value="<?= htmlspecialchars($member['department']) ?>"
                required
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div>
            <label for="batch" class="block text-lg font-semibold text-gray-700 mb-2">Batch <span class="text-red-600">*</span></label>
            <input
                type="text"
                name="batch"
                id="batch"
                value="<?= htmlspecialchars($member['batch']) ?>"
                required
                class="w-full p-3 rounded border border-gray-300 focus:outline-none focus:ring-4 focus:ring-indigo-400 transition"
            />
        </div>

        <div class="flex items-center space-x-4">
            <input
                type="checkbox"
                name="status"
                id="status"
                class="h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                <?= $member['status'] ? "checked" : "" ?>
            />
            <label for="status" class="text-gray-700 font-medium select-none">Active Member</label>
        </div>

        <div>
            <label for="profile_photo" class="block text-lg font-semibold text-gray-700 mb-2">
                Profile Photo (JPG or PNG)
                <?php if ($member['profile_photo']): ?>
                    - Current:
                    <img src="../public/uploads/profile_photos/<?= htmlspecialchars($member['profile_photo']) ?>" alt="Profile" class="inline-block w-16 h-16 rounded object-cover ml-2" />
                <?php endif; ?>
            </label>
            <input
                type="file"
                name="profile_photo"
                id="profile_photo"
                accept=".jpg,.jpeg,.png"
                class="w-full text-indigo-700"
            />
        </div>

        <div class="flex justify-between items-center">
            <button type="submit" name="submit" class="bg-indigo-600 text-white px-8 py-3 rounded shadow hover:bg-indigo-700 transition font-semibold tracking-wide">
                Update Member
            </button>
            <a href="view_members.php" class="text-indigo-600 font-semibold hover:underline">Back to Members List</a>
        </div>
    </form>
</div>

</body>
</html>
