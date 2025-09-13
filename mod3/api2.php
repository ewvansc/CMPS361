<?php
$url = "http://localhost:5005/accounts";

$urlWithParams = $url;

$session = curl_init();

curl_setopt($session, CURLOPT_URL, $urlWithParams);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($session);

if ($response === false){
    echo 'Error: ' . curl_error($session);
} else {
    $responseData = json_decode($response, true);
    header('Content-Type: application/json');
    echo json_encode($responseData, JSON_PRETTY_PRINT);
}

curl_close($session);
?>
