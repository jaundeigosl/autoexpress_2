<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$hostMail = $_ENV['MAIL_ORIGIN'];
$hostMailPass = $_ENV['MAIL_PASS'];
$mailDestiny = $_ENV['MAIL_DESTINY'];

require 'vendor/autoload.php';

// Función para enviar la respuesta JSON al frontend
function sendJsonResponse($code, $message) {
    echo json_encode(['code' => $code, 'message' => $message]);
    exit;
}

$name    = $_POST['name'];
$company = $_POST['company'];
$phone   = $_POST['phone'];
$message = $_POST['message'];

if (empty($name) || empty($phone)) {
    sendJsonResponse(400, "Error: Los campos Nombre y Teléfono son requeridos.");
}

if (!preg_match("/^[A-Za-z]+$/", $name)) {
    sendJsonResponse(400, "Error: Formato de nombre inválido.");
}
if (!preg_match("/^[0-9]+$/", $phone)) {
    sendJsonResponse(400, "Error: Formato de teléfono inválido.");
}

$mail = new PHPMailer(true);

try {
    // Configuración del Servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.ejemplo.com'; // Servidor SMTP (ej: smtp.gmail.com, mail.dominio.com)
    $mail->SMTPAuth   = true;
    $mail->Username   =  $hostMail; // Tu dirección de correo SMTP
    $mail->Password   = $hostMailPass; // Tu contraseña o clave de aplicación
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usar ENCRYPTION_SMTPS para puerto 465
    $mail->Port       = 587; // Usar 465 para ENCRYPTION_SMTPS

    // Destinatarios
    $mail->setFrom($hostMail); // El correo que envía (debe ser el mismo que Username)
    $mail->addAddress($mailDestiny, 'Destinatario'); // El correo donde quieres recibir el mensaje
    // $mail->addReplyTo($email, $name); // Si tu formulario tuviera campo de email

    // Contenido del Correo
    $mail->isHTML(true);
    $mail->Subject = 'Nuevo Mensaje de Contacto del Sitio Web';
    
    $body = "<h2>Detalles del Contacto</h2>";
    $body .= "<p><strong>Nombre:</strong> " . htmlspecialchars($name) . "</p>";
    $body .= "<p><strong>Compañía:</strong> " . htmlspecialchars($company) . "</p>";
    $body .= "<p><strong>Teléfono:</strong> " . htmlspecialchars($phone) . "</p>";
    $body .= "<p><strong>Mensaje:</strong>". htmlspecialchars($message). "</p>";
    // Agrega más detalles del formulario aquí

    $mail->Body = $body;
    $mail->AltBody = "Nombre: $name\nCompañía: $company\nTeléfono: $phone\n Mensaje:$message"; // Versión sin HTML

    $mail->send();
    
    // Éxito: Enviar respuesta al frontend
    sendJsonResponse(200, "Mensaje enviado correctamente.");

} catch (Exception $e) {
    // Error: Enviar respuesta al frontend
    error_log("PHPMailer Error: " . $e->getMessage()); // Registrar el error para debug
    sendJsonResponse(500, "El mensaje no pudo ser enviado. Error: {$mail->ErrorInfo}");
}

?>