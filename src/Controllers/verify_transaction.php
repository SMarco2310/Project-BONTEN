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

        $stmt = $conn->prepare(
            "UPDATE bookings SET status = ? WHERE reference = ?",
        );
        $stmt->bind_param("ss", $status, $reference);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => true,
                "message" => "Payment Successful",
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "DB Update Failed",
            ]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Transaction Failed",
        ]);
    }
}
?>
