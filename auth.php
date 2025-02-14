<?php
header("Content-Type: application/json");

// Conectar a la base de datos
function getDbConnection() {
    return new PDO("mysql:host=localhost;dbname=tu_base_de_datos", "usuario", "contraseña");
}

// Generar o recuperar una API Key
function getOrCreateApiKey($email) {
    $db = getDbConnection();

    // Verificar si el usuario ya tiene una API Key
    $stmt = $db->prepare("SELECT api_key FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        return json_encode(["api_key" => $user['api_key']]);
    } else {
        // Generar una nueva API Key
        $apiKey = bin2hex(random_bytes(16)); // 32 caracteres aleatorios
        $stmt = $db->prepare("INSERT INTO users (email, api_key) VALUES (?, ?)");
        $stmt->execute([$email, $apiKey]);

        return json_encode(["api_key" => $apiKey]);
    }
}

// Obtener la ruta solicitada
$requestPath = strtok($_SERVER["REQUEST_URI"], '?');

// Permitir solicitud con GET en "/auth"
if ($requestPath === "/auth" && $_SERVER["REQUEST_METHOD"] === "GET") {
    if (!isset($_GET['email'])) {
        http_response_code(400);
        echo json_encode(["error" => "Email requerido"]);
        exit;
    }

    echo getOrCreateApiKey($_GET['email']);
    exit;
}

// Si llega aquí, es porque el endpoint no existe
http_response_code(404);
echo json_encode(["error" => "Endpoint no encontrado"]);
?>
