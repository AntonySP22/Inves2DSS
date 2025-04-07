<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

function getGoogleClient() {
    $client = new Google\Client();
    $client->setApplicationName('CitasClinica');
    $client->setAuthConfig(__DIR__ . '/citasclinica.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
    
    // Agregar la URI de redirección
    $redirect_uri = 'http://localhost/Inves2DSS/usuarios/paciente/calendario/callback.php';
    $client->setRedirectUri($redirect_uri);
    
    // Agregar los scopes necesarios
    $client->setScopes([
        Google\Service\Calendar::CALENDAR_EVENTS,
        Google\Service\Calendar::CALENDAR_READONLY
    ]);
    
    return $client;
}

function getAuthUrl() {
    $client = getGoogleClient();
    return $client->createAuthUrl();
}

function handleCallback($code) {
    $client = getGoogleClient();
    $token = $client->fetchAccessTokenWithAuthCode($code);
    
    if(isset($token['error'])) {
        throw new Exception('Error al obtener el token: ' . $token['error']);
    }
    
    return $token;
}