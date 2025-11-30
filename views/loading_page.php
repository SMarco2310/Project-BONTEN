<?php

require_once '../config/security.php';

set_security_headers();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'manager') {
        header("Location: manager_dashboard.php");
    } else {
        header("Location: user_homepage.php");
    }
    exit();
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
                window.location.href = 'index.php';
            }, 3000);
        });
    </script>
<script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z" data-position="3"></script>
</body>
</html>
