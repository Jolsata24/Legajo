<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = (int)$_SESSION['id'];

try {
    $stmt = $pdo->prepare(
        "SELECT d.id, d.nombre_original, d.estado, d.fecha_subida, a.nombre as area_nombre
         FROM documentos d
         LEFT JOIN areas a ON d.id_area_destino = a.id
         WHERE d.id_usuario = ? AND d.id_area_destino IS NOT NULL
         ORDER BY d.fecha_subida DESC"
    );
    $stmt->execute([$id_usuario]);
    $documentos_enviados = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar los documentos: " . $e->getMessage());
}

$page_title = "Mis Documentos Enviados";
require_once '../includes/header_empleado.php';
require_once '../includes/sidebar_empleado.php';
?>

<style>
    .styled-table { width: 100%; border-collapse: collapse; } 
    .styled-table th, .styled-table td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
    .styled-table th { background-color: #f2f2f2; }
    .btn-download { text-decoration: none; color: #007bff; font-weight: bold; }
    
    /* Barra de búsqueda */
    .search-bar {
        margin-bottom: 20px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
        font-size: 16px;
    }

    /* Colores para los estados */
    .estado {
        padding: 5px 10px;
        border-radius: 15px;
        font-weight: 600;
        font-size: 12px;
        text-align: center;
        display: inline-block;
    }
    .estado-pendiente { background-color: #fef9c3; color: #713f12; }
    .estado-observado { background-color: #ffedd5; color: #9a3412; }
    .estado-revisado { background-color: #dcfce7; color: #166534; }
    .estado-rechazado { background-color: #fee2e2; color: #991b1b; }
</style>
<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-share-square"></i> Mis Envíos</h1>
    </header>

    <main class="content">
        <div class="card">
            <h3>Historial de Documentos Enviados</h3>

            <input type="text" id="searchInput" class="search-bar" placeholder="Buscar por nombre, área o estado...">

            <?php if (empty($documentos_enviados)): ?>
                <p>Aún no has enviado ningún documento a un área.</p>
            <?php else: ?>
                <table class="styled-table" id="documentosTable">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Área Destino</th>
                            <th>Fecha de Envío</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos_enviados as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['nombre_original']) ?></td>
                                <td><?= htmlspecialchars($doc['area_nombre'] ?? 'Asignación pendiente') ?></td>
                                <td><?= $doc['fecha_subida'] ?></td>
                                <td>
                                    <span class="estado estado-<?= strtolower(htmlspecialchars($doc['estado'])) ?>">
                                        <?= htmlspecialchars(strtoupper($doc['estado'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="ver_documento_enviado.php?id=<?= $doc['id'] ?>" class="btn-download">Ver Detalles</a>
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
    const table = document.getElementById('documentosTable');
    const rows = table.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        
        // Empezamos desde 1 para saltarnos la cabecera (thead)
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            if (found) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>