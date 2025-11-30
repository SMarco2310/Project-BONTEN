<?php

require_once '../config/security.php';

set_security_headers();

// Get the redirect target from query parameter
$redirect_to = $_GET['redirect'] ?? 'index.php';

// Validate redirect to prevent open redirect vulnerability
$allowed_redirects = [
    'user_homepage.php',
    'manager_dashboard.php',
    'index.php'
];

if (!in_array($redirect_to, $allowed_redirects)) {
    $redirect_to = 'index.php';
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">


    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Loading...</title>

    <link rel="stylesheet" href="../public/css/loader.css">


    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">

</head>

<body>
    <div class="loader">

    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => {
                window.location.href = '<?php echo htmlspecialchars($redirect_to, ENT_QUOTES, 'UTF-8'); ?>';
            }, 3000);
        });
    </script>

<script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z"></script>

</body>
</html>
