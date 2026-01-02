<?php
session_start();
require '../php/db.php';

// 1. Seguridad
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // 2. Consulta FILTRADA (Solo Trámites de Área)
    // El filtro WHERE d.id_area_destino > 0 asegura que NO se muestren archivos de legajo personal
    $sql = "
        SELECT 
            d.id, d.nombre_original, d.nombre_guardado, d.tipo, d.fecha_subida, d.estado, d.id_area_destino,
            u.nombre as usuario_nombre, 
            u.rol as usuario_rol, 
            u.foto as usuario_foto,
            a.nombre as area_destino_nombre
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        LEFT JOIN areas a ON d.id_area_destino = a.id
        WHERE d.id_area_destino IS NOT NULL AND d.id_area_destino > 0
        ORDER BY d.fecha_subida DESC
    ";
    $stmt = $pdo->query($sql);
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$page_title = "Bandeja de Entrada - Secretaria";
$extra_css = "../style/admin_documentos.css";

require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-inbox"></i> Bandeja de Trámites y Solicitudes</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">

        <div class="control-bar">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por usuario, documento o área...">
            </div>
            
            <div class="filter-group">
                <select id="statusFilter" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="validado">Validados</option>
                    <option value="observado">Observados</option>
                    <option value="rechazado">Rechazados</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div style="padding: 50px; text-align: center; color: #6c757d;">
                    <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5; margin-bottom: 15px;"></i>
                    <p>No hay trámites pendientes en la bandeja de entrada.</p>
                </div>
            <?php else: ?>
                <table class="admin-table" id="docsTable">
                    <thead>
                        <tr>
                            <th>Remitente</th>
                            <th>Documento</th>
                            <th>Destino (Área)</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            $ext = strtolower(pathinfo($doc['nombre_guardado'], PATHINFO_EXTENSION));
                            $iconClass = 'fa-file icon-def';
                            if ($ext === 'pdf') $iconClass = 'fa-file-pdf icon-pdf';
                            elseif (in_array($ext, ['doc','docx'])) $iconClass = 'fa-file-word icon-word';
                            elseif (in_array($ext, ['jpg','png','jpeg'])) $iconClass = 'fa-file-image icon-img';

                            $estado = strtolower($doc['estado'] ?? 'pendiente');
                            $badgeClass = 'status-pendiente'; 
                            if ($estado === 'validado' || $estado === 'aprobado') $badgeClass = 'status-validado'; 
                            elseif ($estado === 'rechazado') $badgeClass = 'status-rechazado';
                            elseif ($estado === 'observado') $badgeClass = 'status-observado';

                            $fotoUser = !empty($doc['usuario_foto']) ? "../uploads/usuarios/".$doc['usuario_foto'] : "../img/user.png";
                            $areaDestino = htmlspecialchars($doc['area_destino_nombre'] ?? 'Sin asignar');
                        ?>
                        <tr class="doc-row" data-estado="<?= $estado ?>">
                            <td>
                                <div class="user-cell">
                                    <img src="<?= htmlspecialchars($fotoUser) ?>" alt="Av">
                                    <div class="user-info-mini">
                                        <h4><?= htmlspecialchars($doc['usuario_nombre']) ?></h4>
                                        <span><?= ucfirst($doc['usuario_rol']) ?></span>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <div class="doc-cell">
                                    <i class="fas <?= $iconClass ?>"></i>
                                    <span class="doc-name" title="<?= htmlspecialchars($doc['nombre_original']) ?>">
                                        <?= htmlspecialchars(substr($doc['nombre_original'], 0, 35)) ?>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span style="font-size: 13px; font-weight: 500; color: #444;">
                                    <i class="fas fa-building" style="color:#adb5bd; margin-right:5px;"></i>
                                    <?= $areaDestino ?>
                                </span>
                            </td>

                            <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>

                            <td>
                                <span class="status-badge <?= $badgeClass ?>"><?= ucfirst($estado) ?></span>
                            </td>

                            <td style="text-align: right;">
                                <a href="ver_documento.php?id=<?= $doc['id'] ?>" class="btn-icon" title="Revisar Documento" style="color: var(--color-primario); background: rgba(13, 110, 253, 0.1);">
                                    <i class="fas fa-eye"></i> Revisar
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
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('.doc-row');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();

        tableRows.forEach(row => {
            const textContent = row.innerText.toLowerCase();
            const rowStatus = row.getAttribute('data-estado');
            
            const matchesSearch = textContent.includes(searchTerm);
            const matchesStatus = statusTerm === '' || rowStatus === statusTerm;

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filterTable);
    statusFilter.addEventListener('change', filterTable);
});
</script>

<?php require_once '../includes/footer.php'; ?>