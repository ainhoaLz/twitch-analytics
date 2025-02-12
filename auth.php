<?php

require 'vendor/autoload.php';

function getOrCreateApiKey($email)
{
    $db = new PDO("mysql:host=localhost;dbname=tu_base_de_datos", "usuario", "contraseña");

    //Ver si el usuario ya tiene un api_key
    $stmt = $db->prepare("SELECT api_key FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        return json_encode(["api_key" => $user['api_key']]);
    } else {
        $apiKey = bin2hex(random_bytes(16));
        $stmt = $db->prepare("INSERT INTO users (email, api_key) VALUES (?, ?)");
        $stmt->execute([$email, $apiKey]);

        return json_encode(["api_key" => $apiKey]);
    }
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email'])) {
    header("Content-Type: application/json");
    echo getOrCreateApiKey($data['email']);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Email requerido"]);
}

