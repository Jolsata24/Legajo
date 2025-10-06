<?php
require '../php/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Método no permitido");

$token = $_POST['token'] ?? '';
$clave = $_POST['clave'] ?? '';
$clave_confirm = $_POST['clave_confirm'] ?? '';

if (empty($token) || empty($clave) || $clave !== $clave_confirm) {
    die("Datos inválidos o las contraseñas no coinciden.");
}

// Volvemos a validar el token por seguridad
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("El token es inválido o ha expirado.");
}

// Todo correcto, actualizamos la contraseña
$password_hash = password_hash($clave, PASSWORD_DEFAULT);

// Limpiamos el token para que no se pueda volver a usar
$stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
$stmt->execute([$password_hash, $user['id']]);

// Redirigimos al login con mensaje de éxito
header("Location: ../into/login.html?status=success&msg=" . urlencode("¡Contraseña actualizada! Ya puedes iniciar sesión."));
exit;