<?php

require_once '../../config/security.php';
set_security_headers();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../config/Database.php';

$db = new Database();
$conn = $db->connect();
$user_id = $_SESSION['user_id'];
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

try {
    $stmt = $conn->prepare("
        SELECT
            e.event_id,
            e.name,
            e.event_date,
            e.event_time,
            e.image_path,
            e.location,
            e.city
        FROM rsvps r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.user_id = ?
        AND MONTH(e.event_date) = ?
        AND YEAR(e.event_date) = ?
        AND e.event_date >= CURDATE()
        AND e.status = 'active'
        ORDER BY e.event_date ASC, e.event_time ASC
    ");

    $stmt->bind_param("iii", $user_id, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $stmt->close();
    $db->close();

    echo json_encode([
        'success' => true,
        'events' => $events,
        'month' => $month,
        'year' => $year
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch events: ' . $e->getMessage()
    ]);
}

?>
