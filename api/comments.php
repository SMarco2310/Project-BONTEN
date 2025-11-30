<?php


require_once '../config/security.php';


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


        SELECT c.comment_id, c.comment_text, c.created_at,

               u.full_name as userName, u.profile_picture as userAvatar,

               0 as rating
        FROM comments c
        JOIN users u ON c.user_id = u.user_id

        WHERE c.event_id = ?
        ORDER BY c.created_at DESC


    ");
    $stmt->bind_param("i", $event_id);


    $stmt->execute();


    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {

        $comments[] = [

            'id' => $row['comment_id'],

            'comment' => htmlspecialchars($row['comment_text'], ENT_QUOTES, 'UTF-8'),


            'userName' => htmlspecialchars($row['userName'], ENT_QUOTES, 'UTF-8'),

            'userAvatar' => '../public/assets/' . htmlspecialchars($row['userAvatar'] ?? 'user.jpg', ENT_QUOTES, 'UTF-8'),

            'rating' => 0,


            'timestamp' => $row['created_at']

        ];


    }

    $stmt->close();


    $db->close();

    echo json_encode($comments);


}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);


        echo json_encode(['error' => 'You must be logged in to post a comment']);
        exit();


    }

    $user_id = $_SESSION['user_id'];

    $data = json_decode(file_get_contents('php://input'), true);

    $comment_text = trim($data['comment'] ?? '');

    $event_id = isset($data['event_id']) ? (int)$data['event_id'] : 0;

    if (empty($comment_text) || $event_id === 0) {

        http_response_code(400);


        echo json_encode(['error' => 'Comment text and event ID are required']);

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

    $stmt = $conn->prepare("
        INSERT INTO comments (event_id, user_id, comment_text)

        VALUES (?, ?, ?)

    ");

    $stmt->bind_param("iis", $event_id, $user_id, $comment_text);

    if ($stmt->execute()) {

        $comment_id = $stmt->insert_id;

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

            'comment' => [
                'id' => $comment_id,
                'comment' => htmlspecialchars($comment_text, ENT_QUOTES, 'UTF-8'),
                'userName' => htmlspecialchars($user_data['full_name'], ENT_QUOTES, 'UTF-8'),

                'userAvatar' => '../public/assets/' . htmlspecialchars($user_data['profile_picture'] ?? 'user.jpg', ENT_QUOTES, 'UTF-8'),

                'rating' => 0,
                'timestamp' => date('Y-m-d H:i:s')

            ]

        ];

        echo json_encode($response);

    } else {

        http_response_code(500);
        echo json_encode(['error' => 'Failed to post comment']);


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
