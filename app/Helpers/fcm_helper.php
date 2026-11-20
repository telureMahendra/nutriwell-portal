<?php

function send_fcm($token, $title, $body, $image_url, $notification_id) {

    $payload = [
        "to" => $token,
        "notification" => [
            "title" => $title,
            "body"  => $body,
            "image" => $image_url
        ],
        "data" => [
            "notification_id" => $notification_id,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ]
    ];

    $headers = [
        "Authorization: key=YOUR_FCM_SERVER_KEY",
        "Content-Type: application/json"
    ];

    $ch = curl_init("https://fcm.googleapis.com/fcm/send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_exec($ch);
    curl_close($ch);
}
