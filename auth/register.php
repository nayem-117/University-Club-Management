<?php
session_start();
include_once "../config/db.php";
$error = "";
$success = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] === 'admin' ? 'admin' : 'member';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already exists";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $username, $password_hash, $role);
            if ($insert->execute()) {
                $success = "Registration successful. You can now login.";
            } else {
                $error = "Failed to register user";
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-400 to-purple-600 min-h-screen flex items-center justify-center">
  <div class="bg-white p-10 rounded-lg shadow-lg w-full max-w-md">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Register New User</h1>
    
    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label class="block mb-2 font-semibold text-gray-700">Username</label>
      <input type="text" name="username" required class="mb-4 w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />

      <label class="block mb-2 font-semibold text-gray-700">Password</label>
      <input type="password" name="password" required class="mb-4 w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />

      <label class="block mb-2 font-semibold text-gray-700">Confirm Password</label>
      <input type="password" name="confirm_password" required class="mb-4 w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />

      <label class="block mb-2 font-semibold text-gray-700">Role</label>
      <select name="role" class="mb-6 w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="member" selected>Member</option>
        <option value="admin">Admin</option>
      </select>

      <button type="submit" name="register" class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-3 rounded-lg hover:from-purple-600 hover:to-indigo-700 transition">Register</button>
    </form>

    <p class="mt-6 text-center text-gray-600">Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a></p>
  </div>
</body>
</html>
