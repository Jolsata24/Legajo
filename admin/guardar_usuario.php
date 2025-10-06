<?php
session_start();
require '../php/db.php';

// 1. Seguridad y validaciones
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$clave_sin_hash = trim($_POST['clave'] ?? ''); // ¡IMPORTANTE! Necesitamos la clave en texto plano
$rol = $_POST['rol'] ?? '';
$id_area = !empty($_POST['id_area']) ? (int)$_POST['id_area'] : null;

// ... (Aquí van tus validaciones de campos vacíos, etc.) ...

try {
    // Verificar si el email ya existe
    $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_check->execute([$email]);
    if ($stmt_check->fetch()) {
        header("Location: crear_usuario.php?status=error&msg=" . urlencode("El correo ya está registrado."));
        exit;
    }

    $password_hash = password_hash($clave_sin_hash, PASSWORD_DEFAULT);

    // Insertar el nuevo usuario
    $stmt_insert = $pdo->prepare(
        "INSERT INTO usuarios (nombre, email, password_hash, rol, id_area) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt_insert->execute([$nombre, $email, $password_hash, $rol, $id_area]);
    $id_nuevo_usuario = $pdo->lastInsertId();

    // 2. Redirigir a la página de éxito con los datos necesarios
    // Pasamos el ID del nuevo usuario y la clave (urlencode para seguridad)
    header("Location: crear_usuario_exito.php?id=" . $id_nuevo_usuario . "&clave=" . urlencode($clave_sin_hash));
    exit;

} catch (PDOException $e) {
    header("Location: crear_usuario.php?status=error&msg=" . urlencode("Error en la base de datos."));
    exit;
}
?>