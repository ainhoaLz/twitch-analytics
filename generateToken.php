<?php

function generateToken($data){
    if(!isset($data["email"])){ //email nulo
        http_response_code(400);
        echo json_encode(["error" => "The email is mandatory"]);
    }
    elseif(!filter_var($data["email"], FILTER_VALIDATE_EMAIL)){ //email invalido
        http_response_code(400);
        echo json_encode(["error" => "The email must be a valid email address"]);
    }
    elseif(!isset($data["api_key"])){
        http_response_code(400);
        echo json_encode(["error" => "The api_key is mandatory"]);
    }
    elseif(isset($data["email"]) && isset($data["api_key"]) && filter_var($data["email"], FILTER_VALIDATE_EMAIL)){ //hay un email válido y hay un api_key

        //conectamos a bbdd
        $con = connectToDB();
        //comprobar que el email y api_key estan en la bbdd
        $queryUser = $con->prepare("SELECT id FROM users WHERE email = ? and api_key = ?");
        $queryUser->execute([$data["email"], $data["api_key"]]);
        $user_id = $queryUser->fetchColumn();
        if(empty($user_id)){
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized. API access token is invalid."]);
            return;
        }
        //generamos token
        $token = bin2hex(random_bytes(32));

        //creamos el id del token
        $queryId = "SELECT COALESCE(max(id),0) + 1 AS new_id FROM tokens";
        $stmt = $con->query($queryId);
        $id = $stmt->fetchColumn();

        //insertar en bbdd
        //primero comprobamos si ese usuario ya tiene un token
        $checkQuery = $con->prepare("SELECT id FROM tokens WHERE user_id = ?");
        $checkQuery->execute([$user_id]);
        $userHasToken = $checkQuery->fetchColumn();
        if(!empty($userHasToken)){ //si el usuario ya tiene un token lo borramos para que se cree uno nuevo y no haya duplicados
            $deleteQuery = $con->prepare("DELETE FROM tokens WHERE id = ?");
            $deleteQuery->execute([$userHasToken]);
        }

        $insertQuery = $con->prepare("INSERT INTO tokens (id, user_id, token, created_at, expires_at) VALUES (?,?,?,NOW(),NOW() + INTERVAL '3 days')");

        if(!$insertQuery->execute([$id, $user_id, $token])){
            throw new Exception("Error inserting token to database: ", $insertQuery->errorInfo());
        }
        http_response_code(200);
        echo json_encode(["token" => $token]);
    }
    else{
        http_response_code(500);
        echo json_encode(["error" => "Internal server error."]);
    }
}

?>