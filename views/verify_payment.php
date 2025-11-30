<?php

require_once '../config/security.php';


set_security_headers();


$reference = $_GET['reference'] ?? '';

if (empty($reference)) {


    header("Location: index.php?payment=error");
    exit();


}


$verify_url = '../src/Controllers/verify_transaction.php?reference=' . urlencode($reference);

$ch = curl_init($verify_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($result && isset($result['status']) && $result['status'] === true) {

    header("Location: index.php?payment=success&reference=" . urlencode($reference));

} else {

    header("Location: index.php?payment=failed&reference=" . urlencode($reference));

}


exit();

?>

