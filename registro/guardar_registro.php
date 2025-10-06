<?php
session_start();
require '../php/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

// --- Recibir datos del formulario (igual que antes) ---
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$clave = trim($_POST['clave'] ?? '');
$id_area = !empty($_POST['id_area']) ? (int)$_POST['id_area'] : null;
$rol = 'empleado'; // Rol por defecto

// --- Validaciones (igual que antes) ---
if (empty($nombre) || empty($email) || empty($clave)) {
    header("Location: registro.php?status=error&msg=" . urlencode("Todos los campos son obligatorios."));
    exit;
}
// ... (resto de validaciones) ...

// --- NUEVA LÓGICA PARA MANEJAR LA FOTO DE PERFIL ---
$nombre_foto = null; // Por defecto, no hay foto

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $foto = $_FILES['foto'];
    
    // 1. Validar tipo de archivo
    $permitidos = ['image/jpeg', 'image/png'];
    if (in_array($foto['type'], $permitidos)) {
        
        // 2. Crear un nombre único para la foto
        $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $nombre_foto = 'user_' . time() . '_' . uniqid() . '.' . $extension;
        
        // 3. Definir la carpeta de destino
        $directorio_fotos = '../uploads/usuarios/';
        if (!is_dir($directorio_fotos)) {
            mkdir($directorio_fotos, 0777, true);
        }
        $ruta_destino = $directorio_fotos . $nombre_foto;
        
        // 4. Mover el archivo
        if (!move_uploaded_file($foto['tmp_name'], $ruta_destino)) {
            $nombre_foto = null; // Si falla, no guardamos nada en la BD
        }
    }
}
// --- FIN DE LA NUEVA LÓGICA ---

try {
    // Verificar si el email ya existe (igual que antes)
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: registro.php?status=error&msg=" . urlencode("Este correo ya está en uso."));
        exit;
    }

    $password_hash = password_hash($clave, PASSWORD_DEFAULT);

    // --- ¡SQL ACTUALIZADO! Ahora incluye la columna 'foto' ---
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombre, email, password_hash, rol, id_area, foto) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$nombre, $email, $password_hash, $rol, $id_area, $nombre_foto]);

    // Redirigir al login con mensaje de éxito (igual que antes)
    header("Location: ../into/login.html?status=success&msg=" . urlencode("¡Cuenta creada con éxito! Ya puedes iniciar sesión."));
    exit;

} catch (PDOException $e) {
    header("Location: registro.php?status=error&msg=" . urlencode("Error al crear la cuenta. Inténtalo de nuevo."));
    exit;
}
?>