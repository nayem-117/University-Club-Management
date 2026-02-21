<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/dashboard.php");
    exit();
}

include_once "config/db.php";

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                // Set sessions
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                header("Location: dashboard/dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
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
<title>Login - University Club Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-600 to-blue-400 min-h-screen flex items-center justify-center">
  <div class="bg-white p-10 rounded-lg shadow-lg w-full max-w-md">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Login</h1>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label class="block mb-2 font-semibold text-gray-700">Username</label>
      <input type="text" name="username" autocomplete="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
        class="mb-4 w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" />

      <label class="block mb-2 font-semibold text-gray-700">Password</label>
      <input type="password" name="password" autocomplete="current-password" required 
        class="mb-6 w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" />

      <button type="submit" name="login" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg hover:from-blue-600 hover:to-purple-700 transition font-semibold">
        Login
      </button>
    </form>

    <p class="mt-6 text-center text-gray-600">
      Don't have an account? 
      <a href="register.php" class="text-indigo-600 hover:underline">Register</a>
    </p>
  </div>
</body>
</html>
