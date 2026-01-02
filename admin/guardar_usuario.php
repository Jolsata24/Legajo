<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

// 2. Recibir Datos
$nombre = trim($_POST['nombre'] ?? '');
$email  = trim($_POST['email'] ?? '');
// OJO: En el formulario HTML el campo se llama 'password', no 'clave'
$password_texto = trim($_POST['password'] ?? ''); 
$rol     = $_POST['rol'] ?? 'empleado';
$id_area = !empty($_POST['id_area']) ? (int)$_POST['id_area'] : null;

// Validaciones básicas
if (empty($nombre) || empty($email) || empty($password_texto)) {
    die("Por favor completa todos los campos obligatorios.");
}

// 3. Procesar Foto (Reutilizamos lógica de editar perfil simplificada)
$foto_nombre = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $nuevo_nombre = "user_" . time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/usuarios/" . $nuevo_nombre)) {
            $foto_nombre = $nuevo_nombre;
        }
    }
}

try {
    // Verificar duplicados
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetch()) {
        die("Error: El correo electrónico ya está registrado.");
    }

    // Hash de contraseña
    $password_hash = password_hash($password_texto, PASSWORD_DEFAULT);

    // Insertar
    $sql = "INSERT INTO usuarios (nombre, email, password_hash, rol, id_area, foto) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $email, $password_hash, $rol, $id_area, $foto_nombre]);
    
    $id_nuevo = $pdo->lastInsertId();

    // 4. Redirigir a Éxito (Enviamos clave en texto plano SOLO esta vez para el PDF)
    header("Location: crear_usuario_exito.php?id=" . $id_nuevo . "&pass=" . urlencode($password_texto));
    exit;

} catch (PDOException $e) {
    die("Error en base de datos: " . $e->getMessage());
}
?>