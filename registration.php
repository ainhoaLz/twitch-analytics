<?php
function registerUser($data){
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
        $con = connectToDB();

        //si el email ya tiene un api_key generamos uno nuevo
        $queryEmail = $con->prepare("SELECT id FROM users WHERE email = ?");
        $queryEmail->execute(array($data["email"]));
        $emailExists = $queryEmail->fetchColumn();
        if(!empty($emailExists)){ //el usuario ya tiene una api_key asi que borramos esta para que se cree la nueva
            $deleteQuery = $con->prepare("DELETE FROM users WHERE id = ?");
            $deleteQuery->execute([$emailExists]);
        }

        $queryId = "SELECT COALESCE(max(id),0) + 1 AS new_id FROM users";
        $stmt = $con->query($queryId);
        $id = $stmt->fetchColumn();
        $date = date("Y-m-d H:i:s");

        //insertar en bbdd junto con email
        $insertQuery = $con->prepare("INSERT INTO users (email, api_key, id, created_at) VALUES (?,?,?,?)");

        if(!$insertQuery->execute([$data["email"], $apiKey, $id, $date])){
            throw new Exception("Error inserting user to database: ", $insertQuery->errorInfo());
        }
        http_response_code(200);
        echo json_encode(["api_key" => $apiKey]);
    }
    else{
        http_response_code(500);
        echo json_encode(["error" => "Internal server error."]);
    }
}
?>