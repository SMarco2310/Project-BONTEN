<?php
// Include security configuration to ensure proper session handling
require_once '../config/security.php';

set_security_headers();

// Check if session is active before attempting to destroy it
if (session_status() === PHP_SESSION_ACTIVE) {
    // Clear all session data
    $_SESSION = array();
    
    // Delete the session cookie using the correct session name
    $session_name = session_name();
    if (isset($_COOKIE[$session_name])) {
        $params = session_get_cookie_params();
        setcookie(
            $session_name,
            '',
            time() - 3600,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    // Destroy the session
    session_destroy();
}


if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
       
        sessionStorage.clear();
        localStorage.clear();
        

        window.location.href = 'index.php';
    </script>
    <script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z"></script>
</body>
</html>
