<?php
session_start();
require '../php/db.php';

// 1. Verificar Sesión (Lógica Original)
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$seccion_id = $_GET['id'] ?? null;

if (!$seccion_id) {
    // Redirección segura si no hay ID
    header("Location: mi_legajo.php");
    exit;
}

try {
    // 2. Obtener datos de la Sección (Lógica Original)
    $stmt_seccion = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
    $stmt_seccion->execute([$seccion_id]);
    $seccion = $stmt_seccion->fetch();

    if (!$seccion) die("Sección no encontrada.");

    // 3. Obtener Documentos (Lógica Original)
    $stmt_docs = $pdo->prepare("SELECT * FROM documentos WHERE id_usuario = ? AND id_seccion = ? ORDER BY fecha_subida DESC");
    $stmt_docs->execute([$usuario_id, $seccion_id]);
    $documentos = $stmt_docs->fetchAll();

    // (Opcional) Datos de usuario para header/sidebar ya se manejan en los includes, 
    // pero dejamos la consulta por si necesitas datos específicos extra.
    $stmt_user = $pdo->prepare("SELECT nombre, rol FROM usuarios WHERE id = ?");
    $stmt_user->execute([$usuario_id]);
    $usuario = $stmt_user->fetch();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Configuración de Cabecera y Estilos
$page_title = "Sección: " . $seccion['nombre'];
$extra_css = "../style/seccion_legajo.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    
    <header class="section-header">
        <div class="header-left">
            <a href="mi_legajo.php" class="btn-back-circle" title="Volver a Carpetas">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="section-title">
                <h2><?= htmlspecialchars($seccion['nombre']) ?></h2>
                <span>Gestionando documentos</span>
            </div>
        </div>
        
        <div class="header-right">
            <a href="subir_doc_personal_admin.php?seccion_id=<?= $seccion_id ?>" class="btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </a>
        </div>
    </header>

    <main class="content">
        
        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Esta carpeta está vacía</h3>
                    <p>No has subido documentos a esta sección todavía.</p>
                </div>
            <?php else: ?>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Nombre del Documento</th>
                            <th>Tipo</th>
                            <th>Fecha de Subida</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            // Lógica visual: Detectar extensión para el icono
                            $archivo = $doc['nombre_guardado'] ?? ''; 
                            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                            
                            $iconClass = 'fa-file file-icon def'; // Icono por defecto
                            if ($ext === 'pdf') $iconClass = 'fa-file-pdf file-icon pdf';
                            elseif (in_array($ext, ['jpg','jpeg','png'])) $iconClass = 'fa-file-image file-icon img';
                            elseif (in_array($ext, ['doc','docx'])) $iconClass = 'fa-file-word file-icon word';
                        ?>
                        <tr>
                            <td>
                                <div class="file-info">
                                    <i class="fas <?= $iconClass ?>"></i>
                                    <div>
                                        <span class="file-name"><?= htmlspecialchars($doc['nombre_original']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: #e9ecef; color: #495057;">
                                    <?= htmlspecialchars($doc['tipo']) ?>
                                </span>
                            </td>
                            <td>
                                <i class="far fa-calendar-alt" style="color: #aaa; margin-right: 5px;"></i>
                                <?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])) ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank" class="action-btn" title="Ver Documento">
                                    <i class="fas fa-eye"></i>
                                </a>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>