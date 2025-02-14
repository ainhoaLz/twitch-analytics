<?php

function registerUser(){
    $data = getBody();
    $email = $data["email"];

    if(!isset($email)){ //email nulo
        http_response_code(400);
        echo json_encode(["error" => "The email is mandatory"]);
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){ //email no sigue el formato
        http_response_code(400);
        echo json_encode(["error" => "The email must be a valid email address"]);
    }
    elseif(isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL)){ //hay un email válido

        //generar apiKey
        $apiKey = bin2hex(random_bytes(16));
        // Conecta con la base de datos
        $con = connectToDB();

        $queryPKemail = $con -> prepare("SELECT * FROM users WHERE email = ?");
        $queryPKemail -> execute([$email]);
        $infouser = $queryPKemail->fetch();
        if($infouser != null){
            // Si el email ya existe, devolvemos una nueva API key
            $updateQuery = $con->prepare("UPDATE users SET api_key = ? WHERE email = ?");
            if (!$updateQuery->execute([$apiKey, $email])) {
                throw new Exception("Error updating API key in database: ", $updateQuery->errorInfo());
            }

            // Responder con la nueva API Key
            http_response_code(200);
            echo json_encode(["api_key" => $apiKey]);

        }else{

            // Genera un id sumando 1 al anterior creado. Si no hay, es el 1.
            $queryId = "SELECT COALESCE(max(id),0) + 1 AS new_id FROM users";
            // Hacemos la consulta
            $stmt = $con->query($queryId);
            //$stmt obtiene el primer valor de la primera consulta devuelta
            $id = $stmt->fetchColumn();
            $date = date("Y-m-d H:i:s");

            //insertar en bbdd junto con email
            $insertQuery = $con->prepare("INSERT INTO users (email, api_key, id, created_at) VALUES (?,?,?,?)");

            if (!$insertQuery->execute([$data["email"], $apiKey, $id, $date])) {
                throw new Exception("Error inserting user to database: ", $insertQuery->errorInfo());
            }
            http_response_code(200);
            echo json_encode(["api_key" => $apiKey]);
        }
    } else{
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

function connectToDB(){
    //Funcion que nos conecta a la bbdd
    $host = "c724r43q8jp5nk.cluster-czz5s0kz4scl.eu-west-1.rds.amazonaws.com";
    $database = "d4nk1746t10jil";
    $user = "uk0klbg1uescn";
    $password = "p4408a7b7ac517dbb23e0ff811487d6f0c68576d627ea50312b0c9835cd3e78b5";
    $port = 5432;
    try{
        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        return new PDO($dsn, $user, $password);
    } catch (PDOException $e) {
        echo $e->getMessage();
        return null;
    }

}
?>