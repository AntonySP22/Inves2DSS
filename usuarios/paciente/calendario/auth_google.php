<?php
require_once '../../../vendor/autoload.php';

function getGoogleClient() {
    $client = new Google\Client();
    $client->setApplicationName('CitasClinica');
    $client->setAuthConfig(__DIR__ . '/citasclinica.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
    $client->setScopes([Google\Service\Calendar::CALENDAR_EVENTS]);
    
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