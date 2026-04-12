<?php
//makes use of the textbee.dev gateway 
function sendNotificationSMS($phoneNumber, $message) {
    $apiKey = "40a0f880-1fd1-40b2-98fe-881d5262c78d";
    $deviceId = "69cb495dc1e8463f25bfbf0c";

    $url = "https://api.textbee.dev/api/v1/gateway/devices/{$deviceId}/send-sms";
    
    $payload = json_encode([
        'recipients' => [$phoneNumber],
        'message' => $message,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey, 
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Returns true if the gateway accepted the request
    return ($httpCode == 200 || $httpCode == 201);
}

?>