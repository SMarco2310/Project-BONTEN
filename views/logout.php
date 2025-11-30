<?php
session_start();

// Clear all session variables
$_SESSION = array();

// If there's a session cookie, delete it
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
        // Clear all client-side storage
        sessionStorage.clear();
        localStorage.clear();
        
        // Redirect to login page
        window.location.href = 'index.php';
    </script>
</body>
</html>
