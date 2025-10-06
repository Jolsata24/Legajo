<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="../style/login.css">
</head>
<body>
<main class="login-wrapper">
    <section class="card">
        <h2 id="login-title">Recuperar Contraseña</h2>
        <p style="text-align:center; margin-bottom: 20px;">
            Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        <?php if (!empty($_GET['msg'])): ?>
            <div style="padding: 10px; ...">
                <?= htmlspecialchars(urldecode($_GET['msg'])); ?>
            </div>
        <?php endif; ?>

        <form action="send_reset_link.php" method="post" class="login-form">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <button type="submit" class="btn-submit">Enviar Enlace</button>
        </form>
        <div class="form-footer">
            <a href="../into/login.html">Volver a Iniciar Sesión</a>
        </div>
    </section>
</main>
</body>
</html>