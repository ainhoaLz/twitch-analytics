<?php
// Configuración
$accessToken = "c9v255gz2xi5xij9293ei6vaob999x";
$clientId = "myqofuuwfbtja3s178j5x7osa78xfh";
$streamerId = "39518378";
$url = "https://api.twitch.tv/helix/users?id=$streamerId"; // Endpoint de Twitch

// Inicializar cURL
$ch = curl_init();

// Configurar opciones de cURL
curl_setopt($ch, CURLOPT_URL, $url); // Establecer la URL
curl_setopt($ch, CURLOPT_HTTPGET, true); // Metodo GET
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retornar respuesta como string
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Client-Id: $clientId"
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desactiva la verificación del certificado SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Desactiva la verificación del host SSL


// Ejecutar solicitud
$response = curl_exec($ch);

//manejo de errores en curl
if (curl_errno($ch)) {
    echo 'Error en cURL: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

//header("Content-Type: application/json");

echo $response;

// Cerrar cURL
curl_close($ch);
?>