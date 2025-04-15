<?php

use Exception;

/**
 * @throws \Random\RandomException
 * @throws Exception
 */
function registerUser($data): void
{

    try {
        if (!isset($data["email"]) || trim($data['email']) == '') {
            respondWithError(400, "The email is mandatory");
            return;
        }
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            respondWithError(400, "The email must be a valid email address");
            return;
        }
        $apiKey = bin2hex(random_bytes(16));
        $con = connectToDB();
        if (existsEmail($con, $data["email"])) {
            updateDBWithNewApiKey($con, $apiKey, $data["email"]);
            respondWithApiKey($apiKey);
            return;
        }
        insertNewUserToDB($con, $data["email"], $apiKey, getNewUserId($con), date("Y-m-d H:i:s"));
        respondWithApiKey($apiKey);
        return;
    } catch (Throwable $e) {
        respondWithError(500, "Internal server error.");
    }
}

/**
 * @param string $apiKey
 * @return void
 */
function respondWithApiKey(string $apiKey): void
{
    http_response_code(200);
    echo json_encode(["api_key" => $apiKey]);
}

/**
 * @param PDO|null $con
 * @return mixed
 */
function getNewUserId(?PDO $con): int
{
    $queryId = "SELECT COALESCE(max(id),0) + 1 AS new_id FROM users";
    $stmt = $con->query($queryId);
    return $stmt->fetchColumn();
}

/**
 * @param PDO|null $con
 * @param $email
 * @param string $apiKey
 * @param mixed $userId
 * @param string $date
 * @return void
 * @throws \Exception
 */
function insertNewUserToDB(?PDO $con, $email, string $apiKey, mixed $userId, string $date): void
{
    $insertQuery = $con->prepare("INSERT INTO users (email, api_key, id, created_at) VALUES (?,?,?,?)");

    if (!$insertQuery->execute([$email, $apiKey, $userId, $date])) {
        throw new Exception("Error inserting user to database: ", $insertQuery->errorInfo());
    }
}

/**
 * @param PDO|null $con
 * @param string $apiKey
 * @param $email
 * @return void
 * @throws \Exception
 */
function updateDBWithNewApiKey(?PDO $con, string $apiKey, $email): void
{
    $updateQuery = $con->prepare("UPDATE users SET api_key = ? WHERE email = ?");
    if (!$updateQuery->execute([$apiKey, $email])) {
        throw new Exception("Error updating API key in database");
    }
}

/**
 * @param PDO|null $con
 * @param $email
 * @return bool
 */
function existsEmail(?PDO $con, $email): bool
{
    $queryPKemail = $con->prepare("SELECT * FROM users WHERE email = ?");
    $queryPKemail->execute([$email]);
    return ($queryPKemail->fetch()) != null;
}

/**
 * @return void
 */
