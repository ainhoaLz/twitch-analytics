<?php

use Random\RandomException;
use Exception;

/**
 * @throws Exception
 */
function generateToken($data): void
{
    try {
        if (!isset($data["email"]) || trim($data['email']) == '') {
            respondWithError(400, "The email is mandatory");
            return;
        }
        if (!isset($data["api_key"]) || trim($data['api_key']) === '') {
            respondWithError(400, "The api_key is mandatory");
            return;
        }
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            respondWithError(400, "The email must be a valid email address");
            return;
        }

        $con = connectToDB();
        $user_id = getUserId($con, $data["email"], $data["api_key"]);
        if (empty($user_id)) {
            respondWithError(401, "Unauthorized. API access token is invalid.");
            return;
        }
        $token = bin2hex(random_bytes(32));
        $tokenId = getNewTokenId($con);
        $userHasToken = checkTokenExistence($con, $user_id);
        if (!empty($userHasToken)) {
            deleteExistingToken($con, $userHasToken);
        }
        insertNewTokenToDB($con, $tokenId, $user_id, $token);
        http_response_code(200);
        echo json_encode(["token" => $token]);
        return;
    } catch (Throwable $e) {
        respondWithError(500, "Internal server error.");
    }
}

/**
 * @param PDO|null $con
 * @param int|null $tokenId
 * @param int $user_id
 * @param string $token
 * @return void
 * @throws Exception
 */
function insertNewTokenToDB(?PDO $con, ?int $tokenId, int $user_id, string $token): void
{
    $sqlQuery = "
        INSERT INTO tokens (
            id, user_id, token, created_at, expires_at
        ) VALUES (
            ?,?,?,NOW(),NOW() + INTERVAL '3 days'
        )
    ";
    $insertTokenQuery = $con->prepare($sqlQuery);
    if (!$insertTokenQuery->execute([$tokenId, $user_id, $token])) {
        throw new Exception("Error inserting token to database.");
    }
}


/**
 * @param int $httpCode
 * @param string $message
 * @return void
 */
function respondWithError(int $httpCode, string $message): void
{
    http_response_code($httpCode);
    echo json_encode(["error" => $message]);
}
function getUserId(?PDO $con, string $email, string $api_key): ?int
{
    $queryUser = $con->prepare("SELECT id FROM users WHERE email = ? and api_key = ?");
    $queryUser->execute([$email, $api_key]);
    return $queryUser->fetchColumn() ?: null;
}

function getNewTokenId(?PDO $con): ?int
{
    $queryId = "SELECT COALESCE(max(id),0) + 1 AS new_id FROM tokens";
    $stmt = $con->query($queryId);
    return $stmt->fetchColumn() ?: null;
}
/**
 * @param PDO|null $con
 * @param int $user_id
 * @return int|null
 */
function checkTokenExistence(?PDO $con, int $user_id): ?int
{
    $checkQuery = $con->prepare("SELECT id FROM tokens WHERE user_id = ?");
    $checkQuery->execute([$user_id]);
    return $checkQuery->fetchColumn() ?: null;
}
/**
 * @param PDO|null $con
 * @param int $userHasToken
 * @return void
 */
function deleteExistingToken(?PDO $con, int $userHasToken): void
{
    $deleteQuery = $con->prepare("DELETE FROM tokens WHERE id = ?");
    $deleteQuery->execute([$userHasToken]);
}
