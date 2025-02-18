<?php
include 'getUser.php';
include 'getStreams.php';
include 'getEnrichedStreams.php';
include 'registration.php';
include 'generateToken.php';
include 'database.php';
include 'getTops.php';


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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    return $result["access_token"];
}

header("Content-Type: application/json");

$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$clientSecret = "wp871wusg2nboymbedhwkd9oxkllgu";
$accessToken = getToken($clientId, $clientSecret);

//validación del token de usuario
if($_SERVER["REQUEST_METHOD"]=='GET' && !validateToken()){
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Token is invalid or expired."]);
    exit;
}

$requestPath = strtok($_SERVER["REQUEST_URI"], '?');
switch($requestPath) {
    case "/analytics/user":
        getUsers($accessToken, $clientId, $_GET['id']);
        break;
    case "/analytics/streams":
        getStreams($accessToken, $clientId);
        break;
    case "/analytics/streams/enriched":
        getEnrichedStreams($accessToken, $clientId, $_GET['limit']);
        break;
    case "/analytics/topsofthetops":
        getTopsofthetops($accessToken, $clientId, $_GET['since'] ?? 600);
        break;
    case "/register":
        registerUser(getBody());
        break;
    case "/token":
        generateToken(getBody());
        break;
}

function getBody(){
    //Como estamos enviando el body como un JSON, tenemos que realizar esto para extraer los datos
    //Si usaramos por ejemplo, "application/x-www-form-urlencoded"
    //Podriamos obtener los datos con $_POST['email'], como haciamos con las peticiones get
    $body = file_get_contents("php://input");
    return json_decode($body, true);
}

function validateToken(): bool
{
    $headers = getAllHeaders();
    if(!isset($headers["Authorization"])){
        return false; //no se ha introducido un token
    }
    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    $con = connectToDB();
    $query = $con->prepare("SELECT id FROM tokens WHERE token = ? AND expires_at > NOW()");
    $query->execute([$token]);
    $result = $query->fetchColumn();

    if(empty($result)){
        return false; //la consulta no nos ha devuelto ningun id, por lo que el token no existe
    }

    return true;

}

?>
