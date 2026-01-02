<?php
session_start();
require '../php/db.php';

// 1. Verificar sesión (Secretaria)
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$id_area = $_GET['id_area'] ?? null;

if (!$id_area) {
    header("Location: explorar_areas.php");
    exit;
}

try {
    // 2. Obtener Info del Área
    $stmtArea = $pdo->prepare("SELECT * FROM areas WHERE id = ?");
    $stmtArea->execute([$id_area]);
    $area = $stmtArea->fetch();

    if (!$area) die("Área no encontrada.");

    // 3. Obtener Documentos del Área
    // Unimos con usuarios para saber quién subió el documento
    $sqlDocs = "
        SELECT 
            d.*,
            u.nombre as usuario_nombre,
            u.rol as usuario_rol,
            u.foto as usuario_foto
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        WHERE u.id_area = ?
        ORDER BY d.fecha_subida DESC
    ";
    $stmtDocs = $pdo->prepare($sqlDocs);
    $stmtDocs->execute([$id_area]);
    $documentos = $stmtDocs->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Área: " . $area['nombre'];
// RECICLAJE: Usamos el estilo del Admin
$extra_css = "../style/admin_documento_area.css";

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-folder-open"></i> Documentos por Área</h1>
        <div class="top-actions">
            <a href="explorar_areas.php" class="btn-back" style="color: #6c757d; text-decoration: none; font-weight: 500; margin-right: 15px;">
                <i class="fas fa-arrow-left"></i> Volver a Departamentos
            </a>
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        
        <div class="area-hero">
            <div class="area-icon-large">
                <i class="fas fa-building"></i>
            </div>
            <div class="area-details">
                <h2><?= htmlspecialchars($area['nombre']) ?></h2>
                <p><?= htmlspecialchars($area['descripcion'] ?? 'Gestión de documentos de este departamento.') ?></p>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div style="padding: 50px; text-align: center; color: #6c757d;">
                    <i class="fas fa-file-invoice" style="font-size: 40px; opacity: 0.3; margin-bottom: 15px;"></i>
                    <p>No hay documentos cargados por empleados de esta área.</p>
                </div>
            <?php else: ?>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Subido Por</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            // Lógica de Iconos
                            $ext = strtolower(pathinfo($doc['nombre_guardado'] ?? '', PATHINFO_EXTENSION));
                            $iconClass = 'fa-file'; $iconColor = '#6c757d';
                            if ($ext === 'pdf') { $iconClass = 'fa-file-pdf'; $iconColor = '#dc3545'; }
                            elseif (in_array($ext, ['doc', 'docx'])) { $iconClass = 'fa-file-word'; $iconColor = '#0d6efd'; }
                            elseif (in_array($ext, ['jpg', 'png'])) { $iconClass = 'fa-file-image'; $iconColor = '#198754'; }

                            // Estado
                            $estado = strtolower($doc['estado'] ?? 'pendiente');
                            $badgeClass = 'status-pendiente';
                            if ($estado === 'validado' || $estado === 'aprobado') $badgeClass = 'status-validado';
                            elseif ($estado === 'rechazado') $badgeClass = 'status-rechazado';

                            // Foto Usuario
                            $fotoUser = !empty($doc['usuario_foto']) ? "../uploads/usuarios/" . $doc['usuario_foto'] : "../img/user.png";
                        ?>
                        <tr>
                            <td>
                                <div class="doc-info-cell">
                                    <i class="fas <?= $iconClass ?>" style="color: <?= $iconColor ?>; font-size: 18px;"></i>
                                    <span class="doc-title"><?= htmlspecialchars($doc['titulo'] ?? $doc['nombre_original']) ?></span>
                                </div>
                            </td>

                            <td>
                                <div class="user-info-cell">
                                    <img src="<?= htmlspecialchars($fotoUser) ?>" alt="User">
                                    <div>
                                        <span class="user-name-text"><?= htmlspecialchars($doc['usuario_nombre']) ?></span>
                                        <span class="user-role-text"><?= ucfirst($doc['usuario_rol']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td><?= htmlspecialchars($doc['tipo']) ?></td>
                            <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>
                            
                            <td>
                                <span class="status-badge <?= $badgeClass ?>"><?= ucfirst($estado) ?></span>
                            </td>

                            <td style="text-align: right;">
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank" class="btn-action" title="Ver Documento">
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