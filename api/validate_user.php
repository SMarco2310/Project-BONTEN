<?php
require_once '../config/security.php';

set_security_headers();

header('Content-Type: application/json');

require_once '../config/Database.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if(empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit();
}

$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $stmt->close();
    $db->close();
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

if(password_verify($password, $user['password'])) {
    echo json_encode(['success' => true, 'message' => 'Validation successful']);
} else {

    echo json_encode(['success' => false, 'message' => 'Invalid password']);
}

$db->close();
?>
