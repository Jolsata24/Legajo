<?php
session_start();
require 'db.php';

// 1. Seguridad y validaciones
if (!isset($_SESSION['id'])) {
    die("Acceso denegado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

$id_usuario = (int)$_SESSION['id'];
$rol_usuario = $_SESSION['rol'];
// Construimos la URL de redirección de vuelta a la página de perfil del rol actual
$pagina_perfil = "../" . $rol_usuario . "/editar_perfil.php";

if (!isset($_FILES['nueva_foto']) || $_FILES['nueva_foto']['error'] !== UPLOAD_ERR_OK) {
    header("Location: " . $pagina_perfil . "?status=error&msg=" . urlencode("No se seleccionó ningún archivo."));
    exit;
}

// 2. Procesar el archivo subido
$foto_nueva = $_FILES['nueva_foto'];
$permitidos = ['image/jpeg', 'image/png'];

if (!in_array($foto_nueva['type'], $permitidos)) {
    header("Location: " . $pagina_perfil . "?status=error&msg=" . urlencode("Formato no permitido (solo JPG o PNG)."));
    exit;
}

$directorio_fotos = '../uploads/usuarios/';
$extension = pathinfo($foto_nueva['name'], PATHINFO_EXTENSION);
$nombre_foto_nuevo = 'user_' . $id_usuario . '_' . time() . '.' . $extension;
$ruta_destino = $directorio_fotos . $nombre_foto_nuevo;

try {
    // 3. Obtener el nombre de la foto antigua para borrarla
    $stmt = $pdo->prepare("SELECT foto FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $foto_antigua = $stmt->fetchColumn();

    // 4. Mover el nuevo archivo
    if (move_uploaded_file($foto_nueva['tmp_name'], $ruta_destino)) {
        
        // 5. Actualizar la base de datos
        $stmt_update = $pdo->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
        $stmt_update->execute([$nombre_foto_nuevo, $id_usuario]);

        // 6. Actualizar la sesión
        $_SESSION['foto'] = $nombre_foto_nuevo;

        // 7. Borrar la foto antigua del servidor
        if (!empty($foto_antigua) && file_exists($directorio_fotos . $foto_antigua)) {
            unlink($directorio_fotos . $foto_antigua);
        }
        
        header("Location: " . $pagina_perfil . "?status=success&msg=" . urlencode("¡Foto actualizada!"));
        exit;

    } else {
        header("Location: " . $pagina_perfil . "?status=error&msg=" . urlencode("Error al guardar la nueva foto."));
        exit;
    }
} catch (PDOException $e) {
    header("Location: " . $pagina_perfil . "?status=error&msg=" . urlencode("Error en la base de datos."));
    exit;
}
?>