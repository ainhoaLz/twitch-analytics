<?php

namespace App\Services;

use App\Repositories\TwitchTokenRepository;
use DateTime;
use Illuminate\Http\Request;
use Throwable;

class TwitchTokenService
{
    private ?\PDO $connection;
    private DatabaseConnection $dbConnection;
    private TwitchTokenRepository $twitchTokenRepo;

    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->connection = $this->dbConnection->connect();
        $this->twitchTokenRepo = new TwitchTokenRepository($this->connection);
    }

    public function getToken(string $clientId, string $clientSecret): ?string
    {
        try {
            $storedToken = $this->twitchTokenRepo->getStoredToken();
            if ($storedToken && !$this->isTokenExpired(new DateTime($storedToken['expires_at']))) {
                return $storedToken['token'];
            }

            return $this->getNewToken($clientId, $clientSecret);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function isTokenExpired(DateTime $expiresAt): bool
    {
        $now = new DateTime();
        return $now >= $expiresAt;
    }

    private function getNewToken(string $clientId, string $clientSecret): ?string
    {
        $url = "https://id.twitch.tv/oauth2/token";
        $postData = [
            "client_id" => $clientId,
            "client_secret" => $clientSecret,
            "grant_type" => "client_credentials"
        ];

        $response = $this->getTokenFromTwitch($url, $postData);
        $result = json_decode($response, true);

        if (!isset($result['access_token'], $result['expires_in'])) {
            return null;
        }

        $this->twitchTokenRepo->storeToken($result['access_token'], $result['expires_in']);
        return $result["access_token"];
    }

    private function getTokenFromTwitch(string $url, array $postData): string
    {
        $curlh = curl_init();
        curl_setopt($curlh, CURLOPT_URL, $url);
        curl_setopt($curlh, CURLOPT_POST, true);
        curl_setopt($curlh, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlh, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curlh);
        curl_close($curlh);

        return $response;
    }

    public function isTokenValid(Request $request): bool
    {
        //        $headers = $request->headers->all(); no funciona ahora mismo
        $headers = getAllHeaders();
        if (!isset($headers['Authorization'])) {
            return false;
        }
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        return $this->twitchTokenRepo->isTokenValidInDB($token);
    }
}
