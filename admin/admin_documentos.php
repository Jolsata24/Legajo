<?php
session_start();
require '../php/db.php';

// 1. Verificar Sesión de Admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../into/login.html");
    exit;
}

try {
    // 2. Consulta Global de Documentos
    // Traemos datos del documento, del usuario propietario y de la sección
    $sql = "
        SELECT 
            d.*, 
            u.nombre as usuario_nombre, 
            u.rol as usuario_rol, 
            u.foto as usuario_foto,
            s.nombre as seccion_nombre
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        LEFT JOIN secciones_legajo s ON d.id_seccion = s.id
        ORDER BY d.fecha_subida DESC
    ";
    $stmt = $pdo->query($sql);
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}

$page_title = "Gestión de Documentos";
$extra_css = "../style/admin_documentos.css";

require_once '../includes/header_admin.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main">
    
    <header class="topbar">
        <h1><i class="fas fa-archive"></i> Gestión Global de Documentos</h1>
        <div class="top-actions">
            <span><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
            <a href="../php/logout.php" class="topbar-logout-btn">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </header>

    <main class="content">

        <div class="control-bar">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por usuario, archivo o sección...">
            </div>
            
            <div class="filter-group">
                <select id="statusFilter" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="validado">Validado/Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                </select>
                </div>
        </div>

        <div class="table-container">
            <?php if (empty($documentos)): ?>
                <div style="padding: 40px; text-align: center; color: var(--color-texto-secundario);">
                    <i class="fas fa-file-invoice" style="font-size: 48px; opacity: 0.5; margin-bottom: 15px;"></i>
                    <p>No se encontraron documentos registrados en el sistema.</p>
                </div>
            <?php else: ?>
                <table class="admin-table" id="docsTable">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Documento</th>
                            <th>Sección</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): 
                            // Iconos
                            $ext = strtolower(pathinfo($doc['nombre_guardado'] ?? '', PATHINFO_EXTENSION));
                            $iconClass = 'fa-file icon-def';
                            if ($ext === 'pdf') $iconClass = 'fa-file-pdf icon-pdf';
                            elseif (in_array($ext, ['jpg','png','jpeg'])) $iconClass = 'fa-file-image icon-img';
                            elseif (in_array($ext, ['doc','docx'])) $iconClass = 'fa-file-word icon-word';

                            // Estado
                            $estado = strtolower($doc['estado'] ?? 'pendiente');
                            $badgeClass = 'status-pendiente';
                            if ($estado === 'validado' || $estado === 'aprobado') $badgeClass = 'status-validado';
                            elseif ($estado === 'rechazado') $badgeClass = 'status-rechazado';

                            // Foto Usuario
                            $fotoUser = !empty($doc['usuario_foto']) ? "../uploads/usuarios/".$doc['usuario_foto'] : "../img/user.png";
                        ?>
                        <tr class="doc-row" data-estado="<?= $estado ?>">
                            <td>
                                <div class="user-cell">
                                    <img src="<?= htmlspecialchars($fotoUser) ?>" alt="Avatar">
                                    <div class="user-info-mini">
                                        <h4><?= htmlspecialchars($doc['usuario_nombre']) ?></h4>
                                        <span><?= ucfirst($doc['usuario_rol']) ?></span>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <div class="doc-cell">
                                    <i class="fas <?= $iconClass ?>"></i>
                                    <span class="doc-name"><?= htmlspecialchars($doc['titulo'] ?? $doc['nombre_original']) ?></span>
                                </div>
                            </td>

                            <td>
                                <span style="font-size: 13px; color: #666;">
                                    <?= htmlspecialchars($doc['seccion_nombre'] ?? 'General') ?>
                                </span>
                            </td>

                            <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>

                            <td>
                                <span class="status-badge <?= $badgeClass ?>"><?= ucfirst($estado) ?></span>
                            </td>

                            <td style="text-align: right;">
                                <a href="../uploads/<?= htmlspecialchars($doc['nombre_guardado']) ?>" target="_blank" class="btn-icon" title="Ver Documento">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="../php/eliminar_doc.php?id=<?= $doc['id'] ?>&redirect=admin_docs" 
                                   class="btn-icon delete" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Eliminar este documento permanentemente?');">
                                    <i class="fas fa-trash-alt"></i>
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
            const rowStatus = row.getAttribute('data-estado').toLowerCase();
            
            // Lógica: Debe coincidir con el texto BUSCADO y con el ESTADO seleccionado
            const matchesSearch = textContent.includes(searchTerm);
            const matchesStatus = statusTerm === '' || rowStatus.includes(statusTerm);

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('keyup', filterTable);
    statusFilter.addEventListener('change', filterTable);
});
</script>

<?php require_once '../includes/footer.php'; ?>