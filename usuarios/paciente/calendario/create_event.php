<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../calendario/coreo-envio.php';

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

function createGoogleCalendarEvent($eventDetails, $paciente_id)
{
    $client = new Client();
    $client->setApplicationName('CitasClinica');
    $client->setAuthConfig(__DIR__ . '/citasclinica.json');

    // Obtener el token guardado del paciente
    global $conexion;
    $query = "SELECT access_token FROM google_calendar_auth WHERE usuario_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $paciente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $auth_data = $result->fetch_assoc();

    if (!$auth_data || empty($auth_data['access_token'])) {
        throw new Exception('Usuario no autorizado para Google Calendar');
    }

    $client->setAccessToken($auth_data['access_token']);
    $service = new Calendar($client);

    $event = new Event([
        'summary' => $eventDetails['summary'],
        'location' => $eventDetails['location'],
        'description' => $eventDetails['description'],
        'start' => [
            'dateTime' => $eventDetails['start']['dateTime'],
            'timeZone' => 'America/El_Salvador',
        ],
        'end' => [
            'dateTime' => $eventDetails['end']['dateTime'],
            'timeZone' => 'America/El_Salvador',
        ],
        'attendees' => [
            ['email' => $eventDetails['paciente_email']]
        ],
        'reminders' => [
            'useDefault' => false,
            'overrides' => [
                ['method' => 'popup', 'minutes' => 30],
            ],
        ],
    ]);

    try {
        $createdEvent = $service->events->insert('primary', $event, [
            'sendUpdates' => 'all',
            'supportsAttachments' => true
        ]);

        // Enviar correo de confirmación inmediata
        if ($createdEvent) {
            $to = $eventDetails['paciente_email'];
            $subject = "Confirmación de Cita Médica";
            $message = "
                <html>
                <head>
                    <title>Confirmación de Cita Médica</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { padding: 20px; }
                        .header { background-color: #4e73df; color: white; padding: 10px; }
                        .content { padding: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Su cita médica ha sido programada</h2>
                        </div>
                        <div class='content'>
                            <p><strong>Fecha y hora:</strong> " . date('d/m/Y h:i A', strtotime($eventDetails['start']['dateTime'])) . "</p>
                            <p><strong>Médico:</strong> Dr. " . htmlspecialchars($eventDetails['medico_nombre']) . "</p>
                            <p><strong>Ubicación:</strong> " . htmlspecialchars($eventDetails['location']) . "</p>
                            <p>La cita ha sido agregada a su calendario de Google.</p>
                            <p>Recibirá recordatorios:</p>
                            <ul>
                                <li>30 minutos antes de la cita (notificación en el calendario)</li>
                            </ul>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Asegurarse de que la función enviarCorreo esté disponible
            if (function_exists('enviarCorreo')) {
                enviarCorreo($to, $subject, $message);
            } else {
                error_log('La función enviarCorreo no está disponible');
            }
        }

        return true;
    } catch (Exception $e) {
        error_log('Error al crear evento: ' . $e->getMessage());
        throw new Exception('Error al crear el evento: ' . $e->getMessage());
    }
}
