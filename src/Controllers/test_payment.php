<?php

header("Access-Control-Allow-Origin: *");


header("Content-Type: application/json");


header("Access-Control-Allow-Methods: POST");


$input = json_decode(file_get_contents("php://input"), true);

echo json_encode([


    "status" => true,

    "message" => "Test endpoint working",

    "received_data" => $input,

    "public_key" => "pk_test_d1f61fd4add0486460c5a543b1a51e97015d1207",


    "amount" => 15000,

    "reference" => "BONTEN_TEST_" . time()

]);

?>
