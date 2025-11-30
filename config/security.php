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

/**
 * Validates an uploaded image file
 * @param array $file The uploaded file array from $_FILES
 * @return true|array Returns true if valid, array of errors if invalid
 */
function validate_image_upload($file) {
    $errors = [];
    
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error occurred.';
        return $errors;
    }
    
    // Check file size (5MB max)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $max_size) {
        $errors[] = 'File size must be less than 5MB.';
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        $errors[] = 'File must be a valid image (JPEG, PNG, GIF, or WebP).';
    }
    
    // Check file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $errors[] = 'File extension must be jpg, jpeg, png, gif, or webp.';
    }
    
    // Additional security check - verify it's actually an image
    if (getimagesize($file['tmp_name']) === false) {
        $errors[] = 'File is not a valid image.';
    }
    
    return empty($errors) ? true : $errors;
}

/**
 * Generates a secure filename for uploaded files
 * @param string $original_filename The original filename
 * @return string A secure filename
 */
function generate_secure_filename($original_filename) {
    // Get the file extension
    $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    
    // Generate a unique filename using timestamp and random bytes
    $unique_name = uniqid('img_', true) . '_' . time();
    
    // Clean the filename and add extension
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $unique_name) . '.' . $extension;
}

?>
