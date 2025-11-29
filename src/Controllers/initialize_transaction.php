<?php
// api/initialize_transaction.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
// use Dotenv\Dotenv;

// 1. Load Database
include_once "../../config/Database.php";

// 2. Paystack Config
$secret_key = "sk_test_c7f377097220a7682f335d6558b568e8f2f057b3";
$public_key = "pk_test_d1f61fd4add0486460c5a543b1a51e97015d1207";

// 3. Get Input Data
$input = json_decode(file_get_contents("php://input"), true);
$email = $input["email"] ?? "";
$regular_quantity = $input["regular_quantity"] ?? 0;
$vip_quantity = $input["vip_quantity"] ?? 0;
$event_id = $input["event_id"] ?? 0;

if (empty($email) || ($regular_quantity == 0 && $vip_quantity == 0) || $event_id == 0) {
    echo json_encode(["status" => false, "message" => "Invalid input"]);
    exit();
}

// 4. Get ticket prices from database
$database = new Database();
$conn = $database->connect();

$price_regular = 0;
$price_vip = 0;
$regular_ticket_id = null;
$vip_ticket_id = null;

$stmt = $conn->prepare("SELECT ticket_id, ticket_name, price, quantity, sold FROM tickets WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

while ($ticket = $result->fetch_assoc()) {
    $ticket_name_lower = strtolower($ticket['ticket_name']);
    if (strpos($ticket_name_lower, 'regular') !== false) {
        $price_regular = $ticket['price'];
        $regular_ticket_id = $ticket['ticket_id'];
        // Check availability
        $available = $ticket['quantity'] - $ticket['sold'];
        if ($regular_quantity > $available) {
            echo json_encode(["status" => false, "message" => "Not enough Regular tickets available"]);
            $conn->close();
            exit();
        }
    } elseif (strpos($ticket_name_lower, 'vip') !== false) {
        $price_vip = $ticket['price'];
        $vip_ticket_id = $ticket['ticket_id'];
        // Check availability
        $available = $ticket['quantity'] - $ticket['sold'];
        if ($vip_quantity > $available) {
            echo json_encode(["status" => false, "message" => "Not enough VIP tickets available"]);
            $conn->close();
            exit();
        }
    }
}
$stmt->close();

// Calculate Amount
$amount_ghs = ($regular_quantity * $price_regular) + ($vip_quantity * $price_vip);
$amount_kobo = $amount_ghs * 100;

// 5. Initialize cURL for Paystack
$url = "https://api.paystack.co/transaction/initialize";
$fields = [
    "email" => $email,
    "amount" => $amount_kobo,
    "currency" => "GHS",
    "callback_url" =>
        "http://localhost/project-bonten/views/verify_payment.php", // UPDATE THIS URL
    "metadata" => [
        "event_id" => $event_id,
        "regular_quantity" => $regular_quantity,
        "vip_quantity" => $vip_quantity,
        "regular_ticket_id" => $regular_ticket_id,
        "vip_ticket_id" => $vip_ticket_id,
    ],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $secret_key,
    "Cache-Control: no-cache",
    "Content-Type: application/json",
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(["status" => false, "message" => "cURL Error: " . $err]);
    exit();
}

$response = json_decode($result, true);

// 6. Save to Database using MySQLi
if ($response["status"]) {
    $reference = $response["data"]["reference"];
    $status = "pending";

    // Construct ticket type string (e.g., "Regular: 2, VIP: 1")
    $ticketTypeParts = [];
    if ($regular_quantity > 0) $ticketTypeParts[] = "Regular: $regular_quantity";
    if ($vip_quantity > 0) $ticketTypeParts[] = "VIP: $vip_quantity";
    $ticketType = implode(", ", $ticketTypeParts);

    $total_quantity = $regular_quantity + $vip_quantity;

    // MySQLi Prepared Statement
    $stmt = $conn->prepare(
        "INSERT INTO bookings (reference, email, event_id, ticket_type, quantity, amount, status) VALUES (?, ?, ?, ?, ?, ?, ?)",
    );

    if ($stmt) {
        // Types: s = string, i = integer, d = double
        // order: reference(s), email(s), event_id(i), ticket_type(s), quantity(i), amount(d), status(s)
        $stmt->bind_param(
            "ssiisis",
            $reference,
            $email,
            $event_id,
            $ticketType,
            $total_quantity,
            $amount_ghs,
            $status,
        );

        if ($stmt->execute()) {
            echo json_encode([
                "status" => true,
                "authorization_url" => $response["data"]["authorization_url"],
                "access_code" => $response["data"]["access_code"],
                "reference" => $response["data"]["reference"],
                "public_key" => $public_key,
                "amount" => $amount_kobo
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "DB Execute Error: " . $stmt->error,
            ]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            "status" => false,
            "message" => "DB Prepare Error: " . $conn->error,
        ]);
    }
    $conn->close();
} else {
    echo json_encode(["status" => false, "message" => $response["message"]]);
}
?>
