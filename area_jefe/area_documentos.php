<?php
session_start();
require '../php/db.php';

// 1. Seguridad: Solo jefes
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_area') {
    header("Location: ../into/login.html");
    exit;
}

$id_jefe = $_SESSION['id'];

try {
    // 2. Obtener el área del jefe logueado
    // Asumimos que la tabla usuarios tiene 'id_area'
    $stmtArea = $pdo->prepare("SELECT id_area FROM usuarios WHERE id = ?");
    $stmtArea->execute([$id_jefe]);
    $id_area_jefe = $stmtArea->fetchColumn();

    if (!$id_area_jefe) {
        die("Error: No tienes un área asignada.");
    }

    // 3. Obtener nombre del área (opcional, para el título)
    $stmtNombreArea = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
    $stmtNombreArea->execute([$id_area_jefe]);
    $nombre_area = $stmtNombreArea->fetchColumn();

    // 4. CONSULTA FILTRADA: Documentos para MI área y que ya estén APROBADOS
    // Cambia 'Validado'/'Aprobado' según cómo guardes el estado exacto en tu BD
    $sql = "
        SELECT 
            d.id, d.nombre_original, d.nombre_guardado, d.fecha_subida, d.estado,
            u.nombre as nombre_empleado, u.foto as foto_empleado
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        WHERE d.id_area_destino = ? 
        AND (d.estado = 'Aprobado' OR d.estado = 'Validado')
        ORDER BY d.fecha_subida DESC
    ";
    
    $stmtDocs = $pdo->prepare($sql);
    $stmtDocs->execute([$id_area_jefe]);
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Documentos - " . $nombre_area;
// Reutilizamos estilos de admin o creamos uno propio
$extra_css = "../style/admin_documentos.css";

require_once '../includes/header_jefe.php';
require_once '../includes/sidebar_jefe.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-building"></i> Bandeja de Entrada: <?= htmlspecialchars($nombre_area) ?></h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        
        <div class="control-bar" style="margin-bottom: 20px;">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar documento o empleado...">
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div style="text-align: center; padding: 50px; color: #6c757d;">
                    <i class="fas fa-folder-open" style="font-size: 48px; opacity: 0.5; margin-bottom: 15px;"></i>
                    <p>No hay documentos aprobados en la bandeja de entrada de <?= htmlspecialchars($nombre_area) ?>.</p>
                </div>
            <?php else: ?>
                <table class="admin-table" id="docsTable">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Documento</th>
                            <th>Fecha Recepción</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            $ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
                            $icon = 'fa-file';
                            if($ext=='pdf') $icon='fa-file-pdf';
                            if(in_array($ext,['doc','docx'])) $icon='fa-file-word';
                            
                            $foto = !empty($doc['foto_empleado']) ? "../uploads/usuarios/".$doc['foto_empleado'] : "../img/user.png";
                        ?>
                        <tr class="doc-row">
                            <td>
                                <div class="user-cell">
                                    <img src="<?= htmlspecialchars($foto) ?>" alt="img">
                                    <div class="user-info-mini">
                                        <h4><?= htmlspecialchars($doc['nombre_empleado']) ?></h4>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="doc-cell">
                                    <i class="fas <?= $icon ?>"></i>
                                    <span class="doc-name"><?= htmlspecialchars($doc['nombre_original']) ?></span>
                                </div>
                            </td>
                            <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>
                            <td>
                                <span class="status-badge status-validado">
                                    <i class="fas fa-check-circle"></i> Aprobado
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="../uploads/<?= $doc['nombre_guardado'] ?>" target="_blank" class="btn-icon" title="Ver Documento">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="../uploads/<?= $doc['nombre_guardado'] ?>" download class="btn-icon" title="Descargar">
                                    <i class="fas fa-download"></i>
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

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.doc-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>