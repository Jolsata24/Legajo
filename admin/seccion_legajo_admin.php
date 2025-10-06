<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$seccion_id = $_GET['id'] ?? null;
if (!$seccion_id) die("Sección no especificada.");

try {
    $stmt_seccion = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmt_seccion->execute([$seccion_id]);
    $seccion = $stmt_seccion->fetch();
    if (!$seccion) die("Sección no encontrada.");

    $stmt_docs = $pdo->prepare("SELECT * FROM documentos WHERE id_usuario = ? AND id_seccion = ? ORDER BY fecha_subida DESC");
    $stmt_docs->execute([$usuario_id, $seccion_id]);
    $documentos = $stmt_docs->fetchAll();

    $stmt_user = $pdo->prepare("SELECT nombre, rol FROM usuarios WHERE id = ?");
    $stmt_user->execute([$usuario_id]);
    $usuario = $stmt_user->fetch();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sección: <?= htmlspecialchars($seccion['nombre']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h2>Bienvenido</h2></div>
        <div class="user-info">
            <img src="../img/user2.png" alt="Foto de <?= htmlspecialchars($usuario['nombre']) ?>">
            <h4><?= htmlspecialchars($usuario['nombre']); ?></h4>
            <p><?= htmlspecialchars(ucfirst($usuario['rol'])); ?></p>
        </div>
        <nav class="menu">
            <a href="admin_dashboard.php"><i class="fas fa-home"></i> Inicio</a>
            <a href="mi_legajo.php" class="active"><i class="fas fa-folder-open"></i> Mi Legajo</a>
            <a href="admin_documentos.php"><i class="fas fa-file-alt"></i> Ver Documentos</a>
            <a href="empleados_panel.php"><i class="fas fa-users"></i> Empleados</a>
            <a href="panel_jefes.php"><i class="fas fa-building"></i> Documentos Área</a>
            <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <h1><i class="fas fa-folder"></i> Sección: <?= htmlspecialchars($seccion['nombre']) ?></h1>
        </header>
        
        <a href="mi_legajo.php" class="btn-back" style="text-decoration: none; color: white; background: #333; padding: 10px; border-radius: 5px; display: inline-block; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Volver a Mi Legajo
        </a>

        <main class="content">
            <div class="card">
                <h3><i class="fas fa-file-alt"></i> Documentos en esta sección</h3>
                <?php if (!empty($documentos)): ?>
                    <table class="styled-table">
                        <thead><tr><th>Nombre Original</th><th>Tipo</th><th>Fecha</th><th>Acción</th></tr></thead>
                        <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['nombre_original']) ?></td>
                                <td><?= htmlspecialchars($doc['tipo']) ?></td>
                                <td><?= $doc['fecha_subida'] ?></td>
                                <td><a class="btn-download" href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank">Ver</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No has subido documentos en esta sección aún.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3><i class="fas fa-upload"></i> Subir Nuevo Documento</h3>
                <form action="subir_doc_personal_admin.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="seccion_id" value="<?= $seccion_id ?>">
                    <label for="tipo">Tipo de Documento:</label>
                    <input type="text" name="tipo" id="tipo" required placeholder="Ej: Certificado de estudios">
                    <br><br>
                    <label for="documento">Seleccionar Archivo:</label>
                    <input type="file" name="documento" id="documento" required>
                    <br><br>
                    <button type="submit" style="padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Subir Documento</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>