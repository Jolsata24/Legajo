<?php
session_start();
require '../php/db.php';

// 1. Seguridad: Solo Admin y que los datos lleguen por POST
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

// 2. Recibir y limpiar datos
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$clave = trim($_POST['clave'] ?? '');
$rol = $_POST['rol'] ?? '';
$id_area = !empty($_POST['id_area']) ? (int)$_POST['id_area'] : null;

// 3. Validaciones
if (empty($nombre) || empty($email) || empty($clave) || empty($rol)) {
    header("Location: crear_usuario.php?status=error&msg=" . urlencode("Todos los campos principales son obligatorios."));
    exit;
}
if (strlen($clave) < 6) {
    header("Location: crear_usuario.php?status=error&msg=" . urlencode("La contraseña debe tener al menos 6 caracteres."));
    exit;
}

// Lógica para manejar la foto de perfil (opcional)
$nombre_foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $foto = $_FILES['foto'];
    $permitidos = ['image/jpeg', 'image/png'];
    if (in_array($foto['type'], $permitidos)) {
        $directorio_fotos = '../uploads/usuarios/';
        if (!is_dir($directorio_fotos)) {
            mkdir($directorio_fotos, 0777, true);
        }
        $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $nombre_foto = 'user_' . time() . '_' . uniqid() . '.' . $extension;
        $ruta_destino = $directorio_fotos . $nombre_foto;
        if (!move_uploaded_file($foto['tmp_name'], $ruta_destino)) {
            $nombre_foto = null;
        }
    }
}

try {
    // 4. Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: crear_usuario.php?status=error&msg=" . urlencode("El correo electrónico ya está registrado."));
        exit;
    }

    // 5. Hashear la contraseña
    $password_hash = password_hash($clave, PASSWORD_DEFAULT);

    // 6. Insertar el nuevo usuario
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombre, email, password_hash, rol, id_area, foto) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$nombre, $email, $password_hash, $rol, $id_area, $nombre_foto]);

    header("Location: crear_usuario.php?status=success&msg=" . urlencode("Usuario '" . $nombre . "' creado exitosamente."));
    exit;

} catch (PDOException $e) {
    header("Location: crear_usuario.php?status=error&msg=" . urlencode("Error en la base de datos."));
    exit;
}
?>