<?php
function registerUser($data){

    $email = $data["email"];

    if(!isset($email)){ //email nulo
        http_response_code(400);
        echo json_encode(["error" => "The email is mandatory"]);
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        http_response_code(400);
        echo json_encode(["error" => "The email must be a valid email address"]);
    }
    elseif(isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL)){ //hay un email válido
        //generar apiKey
        $apiKey = bin2hex(random_bytes(16));
        $con = connectToDB();

        $queryPKemail = $con -> prepare("SELECT * FROM users WHERE email = ?");
        $queryPKemail -> execute([$email]);
        $infouser = $queryPKemail->fetch();


        // Si el email ya exite
        if($infouser != null){
            // Si el email ya existe, devolvemos una nueva API key
            $updateQuery = $con->prepare("UPDATE users SET api_key = ? WHERE email = ?");
            if (!$updateQuery->execute([$apiKey, $email])) {
                throw new Exception("Error updating API key in database: ", $updateQuery->errorInfo());
            }

            // Responder con la nueva API Key
            http_response_code(200);
            echo json_encode(["api_key" => $apiKey]);

        }else {
            $queryId = "SELECT COALESCE(max(id),0) + 1 AS new_id FROM users";
            $stmt = $con->query($queryId);
            $id = $stmt->fetchColumn();
            $date = date("Y-m-d H:i:s");

            //insertar en bbdd junto con email
            $insertQuery = $con->prepare("INSERT INTO users (email, api_key, id, created_at) VALUES (?,?,?,?)");

            if (!$insertQuery->execute([$email, $apiKey, $id, $date])) {
                throw new Exception("Error inserting user to database: ", $insertQuery->errorInfo());
            }
            http_response_code(200);
            echo json_encode(["api_key" => $apiKey]);
        }
    }
    else{
        http_response_code(500);
        echo json_encode(["error" => "Internal server error."]);
    }
}
?>