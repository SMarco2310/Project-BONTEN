<?php

require_once '../config/security.php';


set_security_headers();

header('Content-Type: application/json');

require_once '../config/Database.php';

if (!isset($_SESSION['user_id'])) {

    http_response_code(401);

    echo json_encode(['error' => 'You must be logged in to cancel RSVP']);


    exit();

}

$db = new Database();

$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    $event_id = isset($data['event_id']) ? $data['event_id'] : '';

    $user_id = $_SESSION['user_id'];

    if (empty($event_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Event ID is required']);

        exit();
    }

    if (!is_numeric($event_id)) {
        $stmt = $conn->prepare("SELECT event_id FROM events WHERE LOWER(name) = LOWER(?) OR event_id = ? LIMIT 1");
        $stmt->bind_param("ss", $event_id, $event_id);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();


            $event_id = $row['event_id'];


        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Event not found']);


            $stmt->close();

            $db->close();

            exit();


        }
        $stmt->close();

    }

    $stmt = $conn->prepare("DELETE FROM rsvps WHERE event_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {


            echo json_encode([

                'success' => true,

                'message' => 'RSVP cancelled successfully'
            ]);
        } else {
            http_response_code(404);


            echo json_encode(['error' => 'No RSVP found for this event']);
        }

    } else {

        http_response_code(500);


        echo json_encode(['error' => 'Failed to cancel RSVP']);

    }

    $stmt->close();

    $db->close();
} else {

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);


    $db->close();
}
?>
