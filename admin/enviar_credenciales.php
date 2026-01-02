<?php
// admin/enviar_credenciales.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../php/db.php';

session_start();
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado");
}

$id_usuario = $_GET['id'] ?? 0;
// La clave viene en plano (solo justo después de crear) o vacía si es reenvío (en cuyo caso no podemos enviarla, solo avisar)
$clave_texto = $_GET['clave'] ?? null; 

if (!$id_usuario) die("Falta ID");

// Obtener datos usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$user = $stmt->fetch();

if (!$user) die("Usuario no encontrado");

$mail = new PHPMailer(true);

try {
    // --- CONFIGURACIÓN DEL SERVIDOR SMTP ---
    // (Asegúrate de llenar esto con tus datos reales)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'tu_correo@gmail.com'; // <--- PON TU CORREO
    $mail->Password   = 'tu_contraseña_aplicacion'; // <--- PON TU CLAVE APP
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // --- REMITENTE Y DESTINATARIO ---
    $mail->setFrom('sistema@dremh.gob.pe', 'Sistemas DREMH');
    $mail->addAddress($user['email'], $user['nombre']);

    // --- CONTENIDO ---
    $mail->isHTML(true);
    $mail->Subject = 'Bienvenido al Sistema de Legajos DREMH';
    
    // Cuerpo del correo
    $body = "
    <h2>Bienvenido/a {$user['nombre']}</h2>
    <p>Se ha creado su cuenta en el sistema de legajos.</p>
    <p><strong>Usuario:</strong> {$user['email']}</p>";
    
    if ($clave_texto) {
        $body .= "<p><strong>Contraseña:</strong> {$clave_texto}</p>";
        $body .= "<p><em>Por favor cambie su contraseña al ingresar.</em></p>";
    } else {
        $body .= "<p><em>Si olvidó su contraseña, use la opción 'Recuperar Contraseña' en el login.</em></p>";
    }
    
    $body .= "<p><a href='http://tuservidor.com/into/login.html'>Ingresar al Sistema</a></p>";

    $mail->Body = $body;

    $mail->send();
    
    // REDIRECCIÓN INTELIGENTE
    // Si venimos de crear usuario, volvemos a la pantalla de éxito
    if(isset($_GET['ref']) && $_GET['ref'] == 'exito') {
        echo "<script>
            alert('Correo enviado correctamente a {$user['email']}');
            window.location.href = 'crear_usuario_exito.php?id={$id_usuario}&clave=" . urlencode($clave_texto) . "';
        </script>";
    } else {
        // Si venimos del panel de empleados
        echo "<script>
            alert('Correo enviado correctamente.');
            window.location.href = 'empleados_panel.php';
        </script>";
    }

} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}
?>