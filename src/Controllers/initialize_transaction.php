<?php
// api/initialize_transaction.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
// use Dotenv\Dotenv;

// 1. Load Database
require_once "../config/Database.php";

// 2. Paystack Config
$secret_key = "YOUR_PAYSTACK_SECRET_KEY"; // REPLACE THIS

// 3. Get Input Data
$input = json_decode(file_get_contents("php://input"), true);
$email = $input["email"] ?? "";
$quantity = $input["quantity"] ?? 1;

if (empty($email) || empty($quantity)) {
    echo json_encode(["status" => false, "message" => "Invalid input"]);
    exit();
}

// 4. Calculate Amount
$price_per_ticket = 150;
$amount_ghs = $price_per_ticket * $quantity;
$amount_kobo = $amount_ghs * 100;

// 5. Initialize cURL for Paystack
$url = "https://api.paystack.co/transaction/initialize";
$fields = [
    "email" => $email,
    "amount" => $amount_kobo,
    "callback_url" =>
        "http://localhost/project-bonten/views/verify_payment.html", // UPDATE THIS URL
    "metadata" => [
        "ticket_type" => "Regular",
        "quantity" => $quantity,
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
    // Connect using your class
    $database = new Database();
    $conn = $database->connect();

    $reference = $response["data"]["reference"];
    $ticketType = "Regular";
    $status = "pending";

    // MySQLi Prepared Statement
    $stmt = $conn->prepare(
        "INSERT INTO bookings (reference, email, ticket_type, quantity, amount, status) VALUES (?, ?, ?, ?, ?, ?)",
    );

    if ($stmt) {
        // Types: s = string, i = integer, d = double
        // order: reference(s), email(s), ticket_type(s), quantity(i), amount(d), status(s)
        $stmt->bind_param(
            "sssiis",
            $reference,
            $email,
            $ticketType,
            $quantity,
            $amount_ghs,
            $status,
        );

        if ($stmt->execute()) {
            echo json_encode([
                "status" => true,
                "authorization_url" => $response["data"]["authorization_url"],
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
