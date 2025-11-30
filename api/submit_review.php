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

if(!isset($_SESSION['user_id'])) {

    echo json_encode(['success' => false, 'message' => 'User not logged in']);

    exit();

}


$input = json_decode(file_get_contents('php://input'), true);

$event_id = $input['event_id'] ?? 0;
$rating = $input['rating'] ?? 0;

$review_title = $input['review_title'] ?? '';
$review_text = $input['review_text'] ?? '';
$user_id = $_SESSION['user_id'];

if($event_id === 0 || $rating === 0 || empty($review_text)) {


    echo json_encode(['success' => false, 'message' => 'Missing required fields']);


    exit();

}

if($rating < 1 || $rating > 5) {

    echo json_encode(['success' => false, 'message' => 'Invalid rating value']);

    exit();

}


$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT rsvp_id FROM rsvps WHERE user_id = ? AND event_id = ?");

$stmt->bind_param("ii", $user_id, $event_id);


$stmt->execute();


$result = $stmt->get_result();

if($result->num_rows === 0) {

    echo json_encode(['success' => false, 'message' => 'You must attend this event to review it']);

    $stmt->close();

    $db->close();
    exit();


}
$stmt->close();


$full_review_text = $review_title ? $review_title . "\n\n" . $review_text : $review_text;

$check_stmt = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND event_id = ?");


$check_stmt->bind_param("ii", $user_id, $event_id);

$check_stmt->execute();

$check_result = $check_stmt->get_result();

if($check_result->num_rows > 0) {

    $update_stmt = $conn->prepare("UPDATE reviews SET rating = ?, review_text = ? WHERE user_id = ? AND event_id = ?");

    $update_stmt->bind_param("isii", $rating, $full_review_text, $user_id, $event_id);

    if($update_stmt->execute()) {


        echo json_encode(['success' => true, 'message' => 'Review updated successfully']);


    } else {


        echo json_encode(['success' => false, 'message' => 'Failed to update review']);


    }

    $update_stmt->close();

} else {

    $insert_stmt = $conn->prepare("INSERT INTO reviews (event_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");

    $insert_stmt->bind_param("iiis", $event_id, $user_id, $rating, $full_review_text);

    if($insert_stmt->execute()) {

        echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);

    } else {

        echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
    }


    $insert_stmt->close();

}

$check_stmt->close();

$db->close();

?>
