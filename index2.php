<?php
include 'caso1.php';
include 'caso2.php';
include 'caso3.php';

// Función para obtener el token de acceso
function getToken($clientId, $clientSecret) {
    $url = "https://id.twitch.tv/oauth2/token";
    $headers = [
        "client_id" => $clientId,
        "client_secret" => $clientSecret,
        "grant_type" => "client_credentials"
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($headers));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    return $result["access_token"];
}

// Función para obtener la conexión con la base de datos
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

header("Content-Type: application/json");

$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$clientSecret = "q3vb6b3xi9inlhwvuy6gcysvy3lgx1";
$accessToken = getToken($clientId, $clientSecret);

$requestPath = strtok($_SERVER["REQUEST_URI"], '?');
var_dump($requestPath);
// Añadir el caso para /auth para obtener la API Key
switch($requestPath) {
    case "/auth":
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_GET['email'])) {
                http_response_code(400);
                echo json_encode(["error" => "Email requerido"]);
                exit;
            }
            // Llamar a la función para generar o recuperar la API Key
            echo getOrCreateApiKey($_GET['email']);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;
    case "/analytics/user":
        getUsers($accessToken, $clientId, $_GET['id']);
        break;
    case "/analytics/streams":
        getStreams($accessToken, $clientId);
        break;
    case "/analytics/streams/enriched":
        getEnrichedStreams($accessToken, $clientId, $_GET['limit']);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint no encontrado"]);
        break;
}
?>
<?php
