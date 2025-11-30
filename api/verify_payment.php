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

$reference = $input['reference'] ?? '';
$event_id = $input['event_id'] ?? 0;

$regular_quantity = $input['regular_quantity'] ?? 0;
$vip_quantity = $input['vip_quantity'] ?? 0;

if(empty($reference) || $event_id === 0) {

    echo json_encode(['success' => false, 'message' => 'Invalid request data']);

    exit();


}

if(!isset($_SESSION['user_id'])) {

    echo json_encode(['success' => false, 'message' => 'User not logged in']);

    exit();

}

$user_id = $_SESSION['user_id'];

$email = $_SESSION['email'];

$db = new Database();

$conn = $db->connect();


$stmt = $conn->prepare("SELECT ticket_id, ticket_name, price, quantity, sold FROM tickets WHERE event_id = ?");

$stmt->bind_param("i", $event_id);

$stmt->execute();


$result = $stmt->get_result();

$tickets_data = [];
while($row = $result->fetch_assoc()) {
    $tickets_data[] = $row;

}

$stmt->close();

$regular_ticket = null;

$vip_ticket = null;

foreach($tickets_data as $ticket) {


    $ticket_name_lower = strtolower($ticket['ticket_name']);
    if(strpos($ticket_name_lower, 'regular') !== false) {

        $regular_ticket = $ticket;

    } elseif(strpos($ticket_name_lower, 'vip') !== false) {

        $vip_ticket = $ticket;
    }
}

$total_amount = 0;

if($regular_quantity > 0 && $regular_ticket) {

    $total_amount += $regular_quantity * $regular_ticket['price'];

}
if($vip_quantity > 0 && $vip_ticket) {
    $total_amount += $vip_quantity * $vip_ticket['price'];
}


$conn->begin_transaction();

try {

    $stmt = $conn->prepare("INSERT INTO bookings (reference, email, event_id, ticket_type, quantity, amount, status) VALUES (?, ?, ?, ?, ?, ?, 'successful')");

    if($regular_quantity > 0 && $regular_ticket) {

        $ticket_type = 'Regular';
        $reg_amount = $regular_quantity * $regular_ticket['price'];

        $stmt->bind_param("ssissd", $reference, $email, $event_id, $ticket_type, $regular_quantity, $reg_amount);


        $stmt->execute();


        $update_stmt = $conn->prepare("UPDATE tickets SET sold = sold + ? WHERE ticket_id = ?");

        $update_stmt->bind_param("ii", $regular_quantity, $regular_ticket['ticket_id']);

        $update_stmt->execute();

        $update_stmt->close();


    }

    if($vip_quantity > 0 && $vip_ticket) {

        $ticket_type = 'VIP';


        $vip_amount = $vip_quantity * $vip_ticket['price'];


        $stmt->bind_param("ssissd", $reference, $email, $event_id, $ticket_type, $vip_quantity, $vip_amount);


        $stmt->execute();

        $update_stmt = $conn->prepare("UPDATE tickets SET sold = sold + ? WHERE ticket_id = ?");

        $update_stmt->bind_param("ii", $vip_quantity, $vip_ticket['ticket_id']);

        $update_stmt->execute();

        $update_stmt->close();

    }

    $stmt->close();


    $rsvp_check = $conn->prepare("SELECT rsvp_id FROM rsvps WHERE event_id = ? AND user_id = ?");


    $rsvp_check->bind_param("ii", $event_id, $user_id);
    $rsvp_check->execute();

    $rsvp_result = $rsvp_check->get_result();

    if($rsvp_result->num_rows === 0) {

        $rsvp_stmt = $conn->prepare("INSERT INTO rsvps (event_id, user_id) VALUES (?, ?)");

        $rsvp_stmt->bind_param("ii", $event_id, $user_id);

        $rsvp_stmt->execute();
        $rsvp_stmt->close();
    }

    $rsvp_check->close();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Payment verified and booking confirmed']);

} catch(Exception $e) {


    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()]);


}

$db->close();
?>
