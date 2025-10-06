<?php
session_start();
require '../php/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}

$nombre_pdf = $_GET['pdf'] ?? null;
$email_destino = $_GET['email'] ?? null;
$ruta_pdf = '../uploads/credenciales/' . $nombre_pdf;

if (!$nombre_pdf || !$email_destino || !file_exists($ruta_pdf)) {
    die("Error: Faltan datos o el archivo PDF no se encuentra.");
}

$mail = new PHPMailer(true);
try {
    // Configuración del servidor (la misma que usaste para recuperar contraseña)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jolsata24@gmail.com';
    $mail->Password = 'vahm udlb ajvt crld';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Contenido del correo
    $mail->setFrom('no-reply@legajo.com', 'Sistema de Legajos DREMH');
    $mail->addAddress($email_destino);
    $mail->isHTML(true);
    $mail->Subject = 'Bienvenido al Sistema de Legajos';
    $mail->Body    = 'Hola,<br><br>Se ha creado una cuenta para ti. Adjunto encontrarás un documento PDF con tus credenciales de acceso.<br><br>Saludos.';
    
    // ¡Adjuntar el PDF!
    $mail->addAttachment($ruta_pdf);

    $mail->send();

    // Borrar el PDF temporal después de enviarlo
    unlink($ruta_pdf);

    echo "
    <link rel='stylesheet' href='../style/dashboard.css'>
    <div class='main' style='margin-left: 20px;'>
        <div class='card'>
            <h3>🚀 Correo Enviado Exitosamente</h3>
            <p>Las credenciales han sido enviadas a <strong>".htmlspecialchars($email_destino)."</strong>.</p>
            <br>
            <a href='crear_usuario.php'>Crear otro usuario</a> | <a href='admin_dashboard.php'>Volver al Inicio</a>
        </div>
    </div>
    ";

} catch (Exception $e) {
    echo "El mensaje no pudo ser enviado. Mailer Error: {$mail->ErrorInfo}";
}
?>