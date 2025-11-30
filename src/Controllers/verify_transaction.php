<?php

header("Content-Type: application/json");
include_once "../../config/Database.php";

$secret_key = "sk_test_c7f377097220a7682f335d6558b568e8f2f057b3"; // REPLACE THIS

if (isset($_GET["reference"])) {
    $reference = $_GET["reference"];



    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>
            "https://api.paystack.co/transaction/verify/" .
            rawurlencode($reference),
        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $secret_key,
            "Cache-Control: no-cache",
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo json_encode(["status" => false, "message" => "Curl error"]);
        exit();
    }

    $result = json_decode($response, true);

    if ($result["status"] && $result["data"]["status"] === "success") {
        $database = new Database();
        $conn = $database->connect();

        $status = "success";

        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE reference = ?");
        $stmt->bind_param("ss", $status, $reference);
        $stmt->execute();
        $stmt->close();

        // Get booking details and metadata
        $stmt = $conn->prepare("SELECT event_id, email FROM bookings WHERE reference = ?");
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        $booking_result = $stmt->get_result();
        $booking = $booking_result->fetch_assoc();
        $stmt->close();

        if ($booking) {
            $event_id = $booking['event_id'];
            $email = $booking['email'];

            // Get metadata from Paystack response
            $metadata = $result["data"]["metadata"] ?? [];
            $regular_quantity = $metadata["regular_quantity"] ?? 0;
            $vip_quantity = $metadata["vip_quantity"] ?? 0;
            $regular_ticket_id = $metadata["regular_ticket_id"] ?? null;
            $vip_ticket_id = $metadata["vip_ticket_id"] ?? null;

            // Get user_id from email
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user_result = $stmt->get_result();
            $user = $user_result->fetch_assoc();
            $stmt->close();

            if ($user) {
                $user_id = $user['user_id'];

                // Create RSVP if it doesn't exist
                $stmt = $conn->prepare("INSERT IGNORE INTO rsvps (event_id, user_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $event_id, $user_id);
                $stmt->execute();
                $stmt->close();
            }

            // Update ticket sold counts
            if ($regular_quantity > 0 && $regular_ticket_id) {
                $stmt = $conn->prepare("UPDATE tickets SET sold = sold + ? WHERE ticket_id = ?");
                $stmt->bind_param("ii", $regular_quantity, $regular_ticket_id);
                $stmt->execute();
                $stmt->close();
            }

            if ($vip_quantity > 0 && $vip_ticket_id) {
                $stmt = $conn->prepare("UPDATE tickets SET sold = sold + ? WHERE ticket_id = ?");
                $stmt->bind_param("ii", $vip_quantity, $vip_ticket_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        echo json_encode([
            "status" => true,
            "message" => "Payment Successful",
        ]);

        $conn->close();
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Transaction Failed",
        ]);
    }

}
?>
