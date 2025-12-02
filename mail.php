<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 405, 'message' => 'Método no permitido']);
    exit;
}

try {
    
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $hostMail = $_ENV['MAIL_ORIGIN'];
    $hostMailPass = $_ENV['MAIL_PASS'];
    $mailDestiny = $_ENV['MAIL_DESTINY'];
    $name = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($phone)) {
        echo json_encode(['code' => 400, 'message' => 'Complete todos los campos requeridos']);
        exit;
    }
    
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = 'mail.autoexpressamador.com';
    $mail->SMTPAuth = true;
    $mail->Username = $hostMail;     
    $mail->Password = $hostMailPass; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    $mail->SMTPAutoTLS = true;
    $mail->Timeout = 10;
    $mail->CharSet = 'UTF-8';
    
    $mail->setFrom($hostMail, 'Formulario Web - ' . htmlspecialchars($name));
    $mail->addAddress($mailDestiny);
    $mail->addReplyTo($email, $name);
    
    $mail->Subject = 'Nuevo Contacto: ' . htmlspecialchars($service);
    
    $htmlBody = "<h2>Nuevo Contacto - Autoexpress Amador</h2>";
    $htmlBody .= "<p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>";
    $htmlBody .= "<p><strong>Nombre:</strong> " . htmlspecialchars($name) . "</p>";
    $htmlBody .= "<p><strong>Empresa:</strong> " . htmlspecialchars($company) . "</p>";
    $htmlBody .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    $htmlBody .= "<p><strong>Teléfono:</strong> " . htmlspecialchars($phone) . "</p>";
    $htmlBody .= "<p><strong>Servicio:</strong> " . htmlspecialchars($service) . "</p>";
    $htmlBody .= "<p><strong>Mensaje:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
    $htmlBody .= "<hr><p><em>Enviado desde formulario web</em></p>";
    
    $mail->Body = $htmlBody;
    $mail->AltBody = strip_tags(str_replace('<br>', "\n", $htmlBody));
    $mail->isHTML(true);
    
    if ($mail->send()) {
        echo json_encode(['code' => 200, 'message' => '¡Mensaje enviado correctamente!']);
    } else {
        throw new Exception('Error PHPMailer: ' . $mail->ErrorInfo);
    }
    
} catch (Exception $e) {
    // Manejo de errores específicos
    $errorMsg = $e->getMessage();
    
    if (strpos($errorMsg, '535 Incorrect authentication data') !== false) {
        echo json_encode([
            'code' => 500, 
            'message' => 'Error: Credenciales de correo incorrectas.',
            'debug' => 'Verifica usuario y contraseña en .env'
        ]);
    } else if (strpos($errorMsg, 'Could not connect to SMTP host') !== false) {
        echo json_encode([
            'code' => 500, 
            'message' => 'Error de conexión al servidor de correo.',
            'debug' => 'Host: mail.autoexpressamador.com:587'
        ]);
    } else {
        echo json_encode([
            'code' => 500, 
            'message' => 'Error al enviar: ' . substr($errorMsg, 0, 80)
        ]);
    }
}

exit;
?>