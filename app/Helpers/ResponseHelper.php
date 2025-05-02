<?php

if (!function_exists('respondWithError')) {
    function respondWithError(int $httpCode, string $message): array
    {
        return [
            'httpCode' => $httpCode,
            'error' => $message,
        ];
    }
}
