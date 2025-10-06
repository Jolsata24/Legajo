<?php
require '../php/db.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Token no proporcionado.");
}

// Buscar el token en la base de datos y verificar que no haya expirado
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("El token es inválido o ha expirado. Por favor, solicita un nuevo restablecimiento.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Establecer Nueva Contraseña</title>
    <link rel="stylesheet" href="../style/login.css">
</head>
<body>
<main class="login-wrapper">
    <section class="card">
        <h2 id="login-title">Establecer Nueva Contraseña</h2>
        <form action="update_password.php" method="post" class="login-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label for="clave">Nueva Contraseña:</label>
                <input type="password" name="clave" id="clave" required minlength="6">
            </div>
            <div class="form-group">
                <label for="clave_confirm">Confirmar Nueva Contraseña:</label>
                <input type="password" name="clave_confirm" id="clave_confirm" required>
            </div>
            <button type="submit" class="btn-submit">Guardar Contraseña</button>
        </form>
    </section>
</main>
</body>
</html>