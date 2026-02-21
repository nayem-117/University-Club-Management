<?php
session_start();

// Clear all session data
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page after logout
header("Location: login.php"); // login.php assumed also at root
exit();
