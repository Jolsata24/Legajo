<?php
session_start();
require '../php/db.php';

// 1. Verificar sesión (Secretaria)
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$id_area = isset($_GET['id_area']) ? (int)$_GET['id_area'] : 0;

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

    // 3. Obtener Documentos del Área (CORREGIDO)
    // Antes filtraba por u.id_area (dónde trabaja el usuario).
    // Ahora filtra por d.id_area_destino (a dónde va el documento).
    $sqlDocs = "
        SELECT 
            d.*,
            u.nombre as usuario_nombre,
            u.rol as usuario_rol,
            u.foto as usuario_foto
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        WHERE d.id_area_destino = ? 
        ORDER BY d.fecha_subida DESC
    ";
    
    $stmtDocs = $pdo->prepare($sqlDocs);
    $stmtDocs->execute([$id_area]);
    $documentos = $stmtDocs->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Bandeja: " . $area['nombre'];
// RECICLAJE: Usamos el estilo del Admin
$extra_css = "../style/admin_documento_area.css";

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-inbox"></i> Bandeja de Entrada</h1>
        <div class="top-actions">
            <a href="explorar_areas.php" class="btn-back" style="color: #6c757d; text-decoration: none; font-weight: 500; margin-right: 15px;">
                <i class="fas fa-arrow-left"></i> Volver a Departamentos
            </a>
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        
        <div class="area-hero" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 30px; border-radius: 12px; display: flex; align-items: center; gap: 20px; margin-bottom: 30px; border-left: 5px solid var(--color-primario);">
            <div class="area-icon-large" style="font-size: 40px; color: var(--color-primario); background: white; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <i class="fas fa-building"></i>
            </div>
            <div class="area-details">
                <h2 style="margin: 0; font-size: 24px; color: #333;"><?= htmlspecialchars($area['nombre']) ?></h2>
                <p style="margin: 5px 0 0; color: #666;"><?= htmlspecialchars($area['descripcion'] ?? 'Revisión de documentos entrantes para este departamento.') ?></p>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div style="padding: 50px; text-align: center; color: #6c757d; background: white; border-radius: 8px;">
                    <i class="fas fa-check-circle" style="font-size: 48px; opacity: 0.2; margin-bottom: 15px; color: #28a745;"></i>
                    <p style="font-size: 16px;">Todo al día. No hay documentos pendientes para <strong><?= htmlspecialchars($area['nombre']) ?></strong>.</p>
                </div>
            <?php else: ?>
                <table class="doc-table" style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <thead style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                        <tr>
                            <th style="padding: 15px; text-align: left; color: #555;">Documento</th>
                            <th style="padding: 15px; text-align: left; color: #555;">Remitente</th>
                            <th style="padding: 15px; text-align: left; color: #555;">Fecha</th>
                            <th style="padding: 15px; text-align: center; color: #555;">Estado Actual</th>
                            <th style="padding: 15px; text-align: right; color: #555;">Revisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            // Lógica de Iconos
                            $ext = strtolower(pathinfo($doc['nombre_guardado'] ?? '', PATHINFO_EXTENSION));
                            $iconClass = 'fa-file'; $iconColor = '#6c757d';
                            if ($ext === 'pdf') { $iconClass = 'fa-file-pdf'; $iconColor = '#dc3545'; }
                            elseif (in_array($ext, ['doc', 'docx'])) { $iconClass = 'fa-file-word'; $iconColor = '#0d6efd'; }
                            elseif (in_array($ext, ['jpg', 'png', 'jpeg'])) { $iconClass = 'fa-file-image'; $iconColor = '#198754'; }

                            // Estado y Estilos
                            $estado = ucfirst(strtolower($doc['estado'] ?? 'pendiente'));
                            $badgeStyle = 'background: #ffc107; color: #000;'; // Pendiente por defecto
                            
                            if ($estado === 'Aprobado' || $estado === 'Validado') $badgeStyle = 'background: #198754; color: #fff;';
                            elseif ($estado === 'Rechazado') $badgeStyle = 'background: #dc3545; color: #fff;';
                            elseif ($estado === 'Observado') $badgeStyle = 'background: #0dcaf0; color: #000;';

                            // Foto Usuario
                            $fotoUser = !empty($doc['usuario_foto']) ? "../uploads/usuarios/" . $doc['usuario_foto'] : "../img/user.png";
                        ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 15px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas <?= $iconClass ?>" style="color: <?= $iconColor ?>; font-size: 20px;"></i>
                                    <span style="font-weight: 500; color: #333;"><?= htmlspecialchars($doc['nombre_original']) ?></span>
                                </div>
                            </td>

                            <td style="padding: 15px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="<?= htmlspecialchars($fotoUser) ?>" alt="User" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-size: 13px; font-weight: 600;"><?= htmlspecialchars($doc['usuario_nombre']) ?></span>
                                        <span style="font-size: 11px; color: #888;"><?= ucfirst($doc['usuario_rol']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td style="padding: 15px; color: #666; font-size: 13px;">
                                <?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])) ?>
                            </td>
                            
                            <td style="padding: 15px; text-align: center;">
                                <span style="padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; <?= $badgeStyle ?>">
                                    <?= $estado ?>
                                </span>
                            </td>

                            <td style="text-align: right; padding: 15px;">
                                <a href="ver_documento.php?id=<?= $doc['id'] ?>" class="btn-primary" style="text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 13px;">
                                    <i class="fas fa-edit"></i> Revisar
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