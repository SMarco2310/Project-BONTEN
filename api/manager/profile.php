<?php

require_once '../config/security.php';
require_once '../config/image_helpers.php';
set_security_headers();

header('Content-Type: application/json');

require_manager();

require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

$manager_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT user_id, full_name, email, profile_picture, phone_number, created_at
        FROM users
        WHERE user_id = ? AND user_type = 'manager'
    ");

    $stmt->bind_param("i", $manager_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Manager profile not found'
        ]);
        exit();
    }

    $manager = $result->fetch_assoc();
    $stmt->close();

    // Split full name into first and last name
    $nameParts = explode(' ', $manager['full_name'], 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';

    // Get manager statistics
    $stats_stmt = $conn->prepare("
        SELECT
            COUNT(*) as total_events,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_events,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_events
        FROM events
        WHERE manager_id = ?
    ");

    $stats_stmt->bind_param("i", $manager_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    $stats_stmt->close();

    $db->close();

    // Return profile data
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $manager['user_id'],
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $manager['email'],
            'avatar' => get_profile_picture_path($manager['profile_picture'] ?: 'user.jpg'),
            'role' => 'Event Manager',
            'phoneNumber' => $manager['phone_number'],
            'memberSince' => $manager['created_at'],
            'stats' => [
                'totalEvents' => (int)$stats['total_events'],
                'activeEvents' => (int)$stats['active_events'],
                'completedEvents' => (int)$stats['completed_events']
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch manager profile: ' . $e->getMessage()
    ]);
}

?>
