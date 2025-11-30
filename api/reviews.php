<?php

require_once '../config/security.php';
require_once '../config/image_helpers.php';

set_security_headers();

header('Content-Type: application/json');

require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

$action = $_GET['action'] ?? 'list';


$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;


if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {

    if ($event_id === 0) {
        http_response_code(400);

        echo json_encode(['error' => 'Event ID is required']);
        exit();

    }

    $stmt = $conn->prepare("

        SELECT r.review_id, r.rating, r.review_text, r.created_at,

               u.full_name as userName, u.profile_picture as userAvatar

        FROM reviews r
        JOIN users u ON r.user_id = u.user_id

        WHERE r.event_id = ?
        ORDER BY r.created_at DESC

    ");

    $stmt->bind_param("i", $event_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $reviews = [];


    while ($row = $result->fetch_assoc()) {

        $reviews[] = [
            'id' => $row['review_id'],
            'rating' => $row['rating'],
            'review' => htmlspecialchars($row['review_text'], ENT_QUOTES, 'UTF-8'),
            'userName' => htmlspecialchars($row['userName'], ENT_QUOTES, 'UTF-8'),
            'userAvatar' => htmlspecialchars(get_profile_picture_path($row['userAvatar'] ?? 'user.jpg'), ENT_QUOTES, 'UTF-8'),
            'timestamp' => $row['created_at']
        ];

    }

    $stmt->close();

    $db->close();

    echo json_encode($reviews);

}


elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {

    if (!isset($_SESSION['user_id'])) {

        http_response_code(401);

        echo json_encode(['error' => 'You must be logged in to submit a review']);

        exit();


    }

    $user_id = $_SESSION['user_id'];

    $data = json_decode(file_get_contents('php://input'), true);

    $rating = isset($data['rating']) ? (int)$data['rating'] : 0;

    $review_title = trim($data['title'] ?? '');
    $review_text = trim($data['review'] ?? '');

    $event_id = isset($data['eventId']) ? (int)$data['eventId'] : 0;

    if ($rating < 1 || $rating > 5) {
        http_response_code(400);

        echo json_encode(['error' => 'Rating must be between 1 and 5']);

        exit();
    }

    if (empty($review_text) || $event_id === 0) {

        http_response_code(400);


        echo json_encode(['error' => 'Review text and event ID are required']);
        exit();

    }

    $stmt = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");


    $stmt->bind_param("i", $event_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {


        http_response_code(404);

        echo json_encode(['error' => 'Event not found']);

        $stmt->close();

        $db->close();

        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT review_id FROM reviews WHERE event_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {


        http_response_code(400);

        echo json_encode(['error' => 'You have already reviewed this event']);
        $stmt->close();
        $db->close();
        exit();


    }
    $stmt->close();

    $full_review_text = $review_title ? $review_title . "\n\n" . $review_text : $review_text;

    $stmt = $conn->prepare("
        INSERT INTO reviews (event_id, user_id, rating, review_text)
        VALUES (?, ?, ?, ?)

    ");


    $stmt->bind_param("iiis", $event_id, $user_id, $rating, $full_review_text);

    if ($stmt->execute()) {
        $review_id = $stmt->insert_id;

        $user_stmt = $conn->prepare("

            SELECT full_name, profile_picture

            FROM users

            WHERE user_id = ?

        ");
        $user_stmt->bind_param("i", $user_id);

        $user_stmt->execute();

        $user_result = $user_stmt->get_result();

        $user_data = $user_result->fetch_assoc();

        $user_stmt->close();

        $response = [

            'success' => true,

            'review' => [

                'id' => $review_id,
                'rating' => $rating,
                'title' => htmlspecialchars($review_title, ENT_QUOTES, 'UTF-8'),
                'review' => htmlspecialchars($review_text, ENT_QUOTES, 'UTF-8'),
                'userName' => htmlspecialchars($user_data['full_name'], ENT_QUOTES, 'UTF-8'),
                'userAvatar' => htmlspecialchars(get_profile_picture_path($user_data['profile_picture'] ?? 'user.jpg'), ENT_QUOTES, 'UTF-8'),
                'timestamp' => date('Y-m-d H:i:s')

            ]

        ];

        echo json_encode($response);
    } else {
        http_response_code(500);

        echo json_encode(['error' => 'Failed to submit review']);


    }

    $stmt->close();


    $db->close();


}

else {

    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    $db->close();
}

?>
