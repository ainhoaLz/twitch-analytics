<?php


require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


header('Content-Type: application/json');

$pdo = connectToDB();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$apiKey = $data['api_key'] ?? '';

if (!$email || !$apiKey) {
    http_response_code(400);
    echo json_encode(["error" => "Email y API Key son requeridos"]);
    exit;
}

$query = "SELECT id FROM users WHERE email = :email AND api_key = :api_key";
$stmt = $pdo->prepare($query);
$stmt->execute(['email' => $email, 'api_key' => $apiKey]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(["error" => "Credenciales inválidas"]);
    exit;
}

$token = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+3 days'));

$query = "INSERT INTO tokens (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires_at, NOW())";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user['id'], 'token' => $token, 'expires_at' => $expiresAt]);

echo json_encode(["token" => $token, "expires_at" => $expiresAt]);

