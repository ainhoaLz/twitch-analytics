<?php
function registerUser(){
    $data = getBody();
    if(!isset($data["email"])){ //email nulo
        http_response_code(400);
        echo json_encode(["error" => "The email is mandatory"]);
    }
    elseif(!filter_var($data["email"], FILTER_VALIDATE_EMAIL)){
        http_response_code(400);
        echo json_encode(["error" => "The email must be a valid email address"]);
    }
    elseif(isset($data["email"]) && filter_var($data["email"], FILTER_VALIDATE_EMAIL)){ //hay un email válido
        //generar apiKey
        $apiKey = bin2hex(random_bytes(16));
        //conectar a BBDD
        //insertar en bbdd junto con email
        //crear una tabla email - apiKey
        http_response_code(200);
        echo json_encode(["api_key" => $apiKey]);
    }
    else{
        http_response_code(500);
        echo json_encode(["error" => "Internal server error."]);
    }
}

function getBody(){
    //Como estamos enviando el body como un JSON, tenemos que realizar esto para extraer los datos
    //Si usaramos por ejemplo, "application/x-www-form-urlencoded"
    //Podriamos obtener los datos con $_POST['email'], como haciamos con las peticiones get
    $body = file_get_contents("php://input");
    return json_decode($body, true);
}
?>