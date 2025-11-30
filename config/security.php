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

    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';

    $is_api_endpoint = (strpos($script_name, 'api/') !== false ||
                        strpos($script_name, 'Controllers/') !== false ||
                        strpos($request_uri, 'initialize_transaction') !== false ||
                        strpos($request_uri, 'verify_transaction') !== false);

    if ($is_api_endpoint && !headers_sent()) {

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");


        header("Access-Control-Allow-Headers: Content-Type, Authorization");


    }

    if (!$is_api_endpoint) {
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        $is_payment_page = (strpos($request_uri, 'event.php') !== false ||


                           strpos($request_uri, 'payment') !== false ||


                           strpos($script_name, 'event.php') !== false);
    }
}


function validate_image_upload($file) {
    $errors = [];
    
   
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error occurred.';
        return $errors;
    }
    
   
    $max_size = 5 * 1024 * 1024; 
    if ($file['size'] > $max_size) {
        $errors[] = 'File size must be less than 5MB.';
    }
    
   
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        $errors[] = 'File must be a valid image (JPEG, PNG, GIF, or WebP).';
    }
    
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $errors[] = 'File extension must be jpg, jpeg, png, gif, or webp.';
    }
    
   
    if (getimagesize($file['tmp_name']) === false) {
        $errors[] = 'File is not a valid image.';
    }
    
    return empty($errors) ? true : $errors;
}


function generate_secure_filename($original_filename) {
    // Get the file extension
    $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    
    // Generate a unique filename using timestamp and random bytes
    $unique_name = uniqid('img_', true) . '_' . time();
    
    // Clean the filename and add extension
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $unique_name) . '.' . $extension;
}


function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}



function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}


function check_rate_limit($key, $max_attempts, $time_window) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $now = time();
    
    if (isset($_SESSION['rate_limit'][$key])) {
        $attempts = $_SESSION['rate_limit'][$key];
        
        // Clean old attempts
        $attempts = array_filter($attempts, function($timestamp) use ($now, $time_window) {
            return ($now - $timestamp) < $time_window;
        });
        
        if (count($attempts) >= $max_attempts) {
            return false;
        }
    }
    
    return true;
}



function record_attempt($key) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [];
    }
    
    $_SESSION['rate_limit'][$key][] = time();
}




function validate_password($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    return empty($errors) ? true : $errors;
}




function require_manager() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'manager') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized: Manager access required'
        ]);
        exit();
    }
}


function require_user() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized: User access required'
        ]);
        exit();
    }
}


function require_authenticated() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized: Authentication required'
        ]);
        exit();
    }
}


function log_security_event($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;

    $log_file = __DIR__ . '/../logs/security.log';
    $log_dir = dirname($log_file);


    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }


    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);


    if (in_array($level, ['ERROR', 'CRITICAL'])) {
        error_log($log_entry);
    }
}

?>
