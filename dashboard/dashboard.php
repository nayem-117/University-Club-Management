<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Optional: You can also get username for welcome message if you want
$username = $_SESSION['username'] ?? 'Admin';

?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - University Club Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Custom shadow glow for hovered cards */
        .card-hover:hover {
            box-shadow:
                0 4px 10px rgba(59, 130, 246, 0.5),
                0 8px 20px rgba(59, 130, 246, 0.3);
            transform: translateY(-6px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 min-h-screen flex flex-col items-center justify-center py-12 px-6 relative">

    <!-- Logout button top-right -->
    <div class="absolute top-6 right-8 z-50">
        <a href="../logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
            Logout
        </a>
    </div>

    <header class="mb-12 text-center max-w-4xl">
        <h1 class="text-5xl text-white font-extrabold mb-4 drop-shadow-lg tracking-wide">
            Welcome, <?= htmlspecialchars($username) ?>
        </h1>
        <p class="text-indigo-300 text-lg max-w-xl mx-auto">
            Manage all aspects of your club easily with full control over members, committees, events, finances, and meetings.
        </p>
    </header>

    <main class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12 max-w-7xl w-full">

        <!-- Member Management -->
        <a href="../members/view_members.php" 
           class="card-hover bg-gradient-to-tr from-indigo-600 via-indigo-500 to-indigo-400 rounded-3xl shadow-xl p-10 flex flex-col items-center text-white transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A6.972 6.972 0 0112 15a6.972 6.972 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-2xl font-bold tracking-wide">Member Management</span>
            <p class="mt-3 text-indigo-200 text-center max-w-xs">Add, edit, and monitor all club members efficiently.</p>
        </a>

        <!-- Club Executive Committee -->
        <a href="../committee/view_committee_members.php" 
           class="card-hover bg-gradient-to-tr from-purple-600 via-purple-500 to-purple-400 rounded-3xl shadow-xl p-10 flex flex-col items-center text-white transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 11-8 0 4 4 0 018 0zM12 14v7" />
            </svg>
            <span class="text-2xl font-bold tracking-wide">Club Executive Committee</span>
            <p class="mt-3 text-purple-200 text-center max-w-xs">Manage roles, terms, and leadership of your executive committee.</p>
        </a>

        <!-- Event Management -->
        <a href="../events/view_events.php" 
           class="card-hover bg-gradient-to-tr from-green-600 via-green-500 to-green-400 rounded-3xl shadow-xl p-10 flex flex-col items-center text-white transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3M16 7V3M4 11h16M4 19h16M4 15h16M5 21h14a2 2 0 002-2v-6H3v6a2 2 0 002 2z" />
            </svg>
            <span class="text-2xl font-bold tracking-wide">Event Management</span>
            <p class="mt-3 text-green-200 text-center max-w-xs">Create and organize events with participant tracking and details.</p>
        </a>

        <!-- Finance Management -->
        <a href="../finance/view_finance.php" 
           class="card-hover bg-gradient-to-tr from-teal-600 via-teal-500 to-teal-400 rounded-3xl shadow-xl p-10 flex flex-col items-center text-white transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 00-2-2H7a2 2 0 01-2-2V9m14 8v-2a2 2 0 00-2-2h-1a2 2 0 01-2-2V9m5 8v6m-5-6v6" />
            </svg>
            <span class="text-2xl font-bold tracking-wide">Finance Management</span>
            <p class="mt-3 text-teal-200 text-center max-w-xs">Track income, expenses, budgets, and generate financial reports.</p>
        </a>

        <!-- Meeting Records -->
        <a href="../meetings/view_meetings.php" 
           class="card-hover bg-gradient-to-tr from-indigo-600 via-indigo-500 to-indigo-400 rounded-3xl shadow-xl p-10 flex flex-col items-center text-white transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 00-2-2H7a2 2 0 012-2m10 12V5" />
            </svg>
            <span class="text-2xl font-bold tracking-wide">Meeting Records</span>
            <p class="mt-3 text-indigo-200 text-center max-w-xs">Schedule meetings and manage attendance efficiently.</p>
        </a>

    </main>

    <footer class="mt-20 text-indigo-300 font-semibold text-center">
        &copy; <?= date("Y") ?> University Club Management System. All rights reserved.
    </footer>

</body>
</html>
