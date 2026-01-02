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
    $stmtArea = $pdo->prepare("SELECT id_area FROM usuarios WHERE id = ?");
    $stmtArea->execute([$id_jefe]);
    $id_area_jefe = $stmtArea->fetchColumn();

    if (!$id_area_jefe) {
        die("Error: No tienes un área asignada.");
    }

    // 3. Obtener nombre del área
    $stmtNombreArea = $pdo->prepare("SELECT nombre FROM areas WHERE id = ?");
    $stmtNombreArea->execute([$id_area_jefe]);
    $nombre_area = $stmtNombreArea->fetchColumn();

    // 4. CONSULTA FILTRADA CORREGIDA
    // Usamos UPPER() para evitar problemas si se guardó como 'aprobado' o 'Aprobado'.
    // Filtramos para ver SOLO lo que ya fue aprobado por secretaría.
    // ... (parte superior de tu archivo) ...

    // 4. CONSULTA FILTRADA CORREGIDA
    // Filtramos ignorando mayúsculas/minúsculas
    $sql = "
        SELECT 
            d.id, d.nombre_original, d.nombre_guardado, d.fecha_subida, d.estado,
            u.nombre as nombre_empleado, u.foto as foto_empleado
        FROM documentos d
        JOIN usuarios u ON d.id_usuario = u.id
        WHERE d.id_area_destino = ? 
        AND (UPPER(d.estado) = 'APROBADO' OR UPPER(d.estado) = 'VALIDADO')
        ORDER BY d.fecha_subida DESC
    ";
// ... (resto del archivo) ...
    
    $stmtDocs = $pdo->prepare($sql);
    $stmtDocs->execute([$id_area_jefe]);
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Documentos - " . $nombre_area;
require_once '../includes/header_jefe.php';
require_once '../includes/sidebar_jefe.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-building"></i> Bandeja de Entrada: <?= htmlspecialchars($nombre_area) ?></h1>
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
                    <i class="fas fa-check-double" style="font-size: 48px; opacity: 0.5; margin-bottom: 15px;"></i>
                    <p>No hay documentos aprobados pendientes de revisión en <?= htmlspecialchars($nombre_area) ?>.</p>
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
                                    <img src="<?= htmlspecialchars($foto) ?>" alt="img" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                                    <div class="user-info-mini">
                                        <span style="font-weight:bold;"><?= htmlspecialchars($doc['nombre_empleado']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="doc-cell">
                                    <i class="fas <?= $icon ?>" style="margin-right:8px;color:#555;"></i>
                                    <?= htmlspecialchars($doc['nombre_original']) ?>
                                </div>
                            </td>
                            <td><?= date("d/m/Y", strtotime($doc['fecha_subida'])) ?></td>
                            <td>
                                <span class="status-badge" style="background:#d4edda;color:#155724;padding:4px 8px;border-radius:4px;font-size:0.85em;">
                                    <i class="fas fa-check-circle"></i> Aprobado
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="../uploads/<?= $doc['nombre_guardado'] ?>" target="_blank" class="btn-icon" title="Ver" style="margin-right:10px;color:#007bff;"><i class="fas fa-eye"></i></a>
                                <a href="../uploads/<?= $doc['nombre_guardado'] ?>" download class="btn-icon" title="Descargar" style="color:#28a745;"><i class="fas fa-download"></i></a>
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