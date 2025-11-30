<?php
require_once '../config/security.php';
set_security_headers();

// Get the reference from Paystack callback
$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    header("Location: index.php?payment=error");
    exit();
}

// Verify the payment by calling the verification endpoint
$verify_url = '../src/Controllers/verify_transaction.php?reference=' . urlencode($reference);
$ch = curl_init($verify_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($result && isset($result['status']) && $result['status'] === true) {
    // Payment successful - redirect to a success page or back to event
    header("Location: index.php?payment=success&reference=" . urlencode($reference));
} else {
    // Payment failed
    header("Location: index.php?payment=failed&reference=" . urlencode($reference));
}
exit();
?>

