<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_name('BONTEN_SESSION');
    session_start();

    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

function set_security_headers() {
    // Don't set X-Frame-Options to allow Paystack popups, or set it to SAMEORIGIN
    // Note: X-Frame-Options SAMEORIGIN still allows same-origin iframes, but Paystack uses cross-origin
    // We'll use CSP frame-ancestors instead and omit X-Frame-Options for payment pages
    
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    
    // For payment/API endpoints, allow cross-origin requests
    $is_api_endpoint = (strpos($script_name, 'api/') !== false || 
                        strpos($script_name, 'Controllers/') !== false ||
                        strpos($request_uri, 'initialize_transaction') !== false ||
                        strpos($request_uri, 'verify_transaction') !== false);
    
    // Set CORS for API endpoints
    if ($is_api_endpoint && !headers_sent()) {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
    }
    
    // Only set restrictive headers for non-API pages
    if (!$is_api_endpoint) {
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        // Allow Paystack domains in CSP - more permissive for payment functionality
        // Note: frame-ancestors allows the page to be embedded, frame-src allows iframes in the page
        $is_payment_page = (strpos($request_uri, 'event.php') !== false || 
                           strpos($request_uri, 'payment') !== false ||
                           strpos($script_name, 'event.php') !== false);
        
        // More permissive CSP for Paystack - allow all Paystack subdomains
        $csp = "default-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://fonts.gstatic.com https://cdn.userway.org https://js.paystack.co https://api.paystack.co https://*.paystack.co https://*.paystack.com; " .
               "img-src 'self' data: https:; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.userway.org https://js.paystack.co https://api.paystack.co https://*.paystack.co https://*.paystack.com; " .
               "frame-src 'self' https://standard.paystack.co https://paystack.com https://*.paystack.co https://*.paystack.com https://checkout.paystack.com https://checkout.paystack.com/*; " .
               "frame-ancestors 'self'; " .
               "connect-src 'self' https://api.paystack.co https://*.paystack.co https://*.paystack.com https://api.paystack.co/*;";
        header("Content-Security-Policy: " . $csp);
        header("X-Powered-By: ");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        // Don't set X-Frame-Options for payment pages as it conflicts with Paystack iframes
        // X-Frame-Options would block Paystack's cross-origin iframes
        // Only set for non-payment pages
        if (!$is_payment_page) {
            header("X-Frame-Options: SAMEORIGIN");
        }
        // For payment pages, rely on CSP frame-ancestors instead
    } else {
        // For API endpoints, set minimal headers
        header("Content-Type: application/json");
    }
}
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function escape_js($string) {
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function escape_url($string) {
    return urlencode($string);
}

function sanitize_input($input) {
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_password($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }

    return empty($errors) ? true : $errors;
}

function check_rate_limit($identifier, $max_attempts = 5, $time_window = 900) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }

    $current_time = time();

    if (isset($_SESSION['rate_limit'][$identifier])) {
        $_SESSION['rate_limit'][$identifier] = array_filter(
            $_SESSION['rate_limit'][$identifier],
            function($timestamp) use ($current_time, $time_window) {
                return ($current_time - $timestamp) < $time_window;
            }
        );
    } else {
        $_SESSION['rate_limit'][$identifier] = [];
    }

    if (count($_SESSION['rate_limit'][$identifier]) >= $max_attempts) {
        return false;
    }

    return true;
}

function record_attempt($identifier) {
    if (!isset($_SESSION['rate_limit'][$identifier])) {
        $_SESSION['rate_limit'][$identifier] = [];
    }
    $_SESSION['rate_limit'][$identifier][] = time();
}

function validate_image_upload($file) {
    $errors = [];

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = "No file uploaded";
        return $errors;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = "File size must be less than 5MB";
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
        $errors[] = "Invalid file format. Only JPG, PNG, GIF, and WEBP are allowed";
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!in_array($mime_type, $allowed_mime_types)) {
        $errors[] = "Invalid file type detected";
    }

    return empty($errors) ? true : $errors;
}

function generate_secure_filename($original_name) {
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
}

function log_security_event($message, $level = 'INFO') {
    $log_file = __DIR__ . '/../logs/security.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $log_message = "[{$timestamp}] [{$level}] IP: {$ip} | {$message} | User-Agent: {$user_agent}\n";

    error_log($log_message, 3, $log_file);
}

function is_authenticated() {
    return isset($_SESSION['user_id']);
}

function is_manager() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'manager';
}

function require_auth() {
    if (!is_authenticated()) {
        log_security_event("Unauthorized access attempt to: " . $_SERVER['REQUEST_URI'], 'WARNING');
        // Use relative path that works from views directory
        header("Location: index.php");
        exit();
    }
}

function require_manager() {
    require_auth();
    if (!is_manager()) {
        log_security_event("Non-manager tried to access manager resource: " . $_SERVER['REQUEST_URI'], 'WARNING');
        // Use relative path that works from views directory
        header("Location: user_homepage.php");
        exit();
    }
}

?>
