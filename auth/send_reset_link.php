<?php
require '../php/db.php';
require '../vendor/autoload.php'; // Carga PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Email inválido.");
}

// 1. Verificar si el usuario existe
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // 2. Generar un token seguro
    $token = bin2hex(random_bytes(32));
    $expires = new DateTime('now + 1 hour'); // El token expira en 1 hora
    $expires_str = $expires->format('Y-m-d H:i:s');

    // 3. Guardar el token en la base de datos
    $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
    $stmt->execute([$token, $expires_str, $user['id']]);

    // 4. Enviar el correo
    $mail = new PHPMailer(true);
    try {
        // --- AÑADE ESTA LÍNEA PARA DEPURAR SI SIGUE SIN LLEGAR EL CORREO ---
        $mail->SMTPDebug = 2; // Muestra toda la conversación con el servidor de Gmail
        
        // --- Configuración del servidor de correo (Ejemplo con Gmail) ---
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jolsata24@gmail.com'; // TU CORREO DE GMAIL
        $mail->Password = 'vahm udlb ajvt crld'; // TU CONTRASEÑA DE APLICACIÓN
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // --- Contenido del correo ---
        $mail->setFrom('no-reply@legajo.com', 'Sistema de Legajo');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Restablecimiento de Contrasena';
        // Asegúrate que la URL sea la correcta para tu XAMPP
        $resetLink = "http://localhost/milegajo/auth/reset_password.php?token=" . $token; 
        $mail->Body    = "Hola,<br><br>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace:<br><a href='{$resetLink}'>Restablecer Contraseña</a><br><br>Si no solicitaste esto, ignora este correo.";
        
        $mail->send();
    } catch (Exception $e) {
        // No mostramos el error al usuario final por seguridad, pero sí en la depuración.
    }
} // <-- ¡ESTA ES LA LLAVE QUE FALTABA! Cierra el bloque 'if ($user)'

// Por seguridad, siempre mostramos un mensaje genérico, exista o no el correo.
header("Location: forgot_password.php?status=success&msg=" . urlencode("Si tu correo está en nuestro sistema, recibirás un enlace para restablecer tu contraseña."));
exit;