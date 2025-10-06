<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['rrhh', 'admin'])) {
    header("Location: ../into/login.html");
    exit;
}

$id_usuario = $_SESSION['id'];

// Obtenemos datos del usuario logueado
$stmt = $pdo->prepare("SELECT u.nombre, u.email, u.rol, u.foto, a.nombre AS area
                       FROM usuarios u
                       LEFT JOIN areas a ON u.id_area = a.id
                       WHERE u.id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Ruta de la foto
$foto_usuario = (!empty($usuario['foto']) && file_exists("../uploads/usuarios/" . $usuario['foto']))
    ? "../uploads/usuarios/" . $usuario['foto']
    : "../img/user2.png";

$nombre_usuario = $usuario['nombre'];
$rol_usuario    = $usuario['rol'];
$area_usuario   = $usuario['area'] ?? "Sin área";

// Traer documentos
try {
    $stmt = $pdo->query("
        SELECT d.id, d.nombre_original, d.tipo, d.fecha_subida, d.estado,
               u.nombre AS empleado_nombre, u.email
        FROM documentos d
        INNER JOIN usuarios u ON d.id_usuario = u.id
        ORDER BY d.fecha_subida DESC
    ");
    $documentos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

$page_title = "Ver Documentos";
require_once '../includes/header_admin.php'; // Asumiendo que tienes un header para admin
require_once '../includes/sidebar_admin.php';
?>

<style>
    .styled-table { width: 100%; border-collapse: collapse; }
    .styled-table th, .styled-table td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
    .styled-table th { background-color: #f2f2f2; }
    
    .search-bar {
        margin-bottom: 20px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
        font-size: 16px;
    }

    .estado {
        padding: 5px 10px; border-radius: 15px; font-weight: 600;
        font-size: 12px; text-align: center; display: inline-block;
    }
    .estado-pendiente { background-color: #fef9c3; color: #713f12; }
    .estado-observado { background-color: #ffedd5; color: #9a3412; }
    .estado-revisado { background-color: #dcfce7; color: #166534; }
    .estado-rechazado { background-color: #fee2e2; color: #991b1b; }
</style>
<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-file-alt"></i>  Documentos de Empleados</h1>
    </header>

    <main class="content">
        <div class="card">
            <input type="text" id="searchInput" class="search-bar" placeholder="Buscar por empleado, email, nombre de documento...">

            <?php if (count($documentos) > 0): ?>
              <table class="styled-table" id="documentosTable">
                <thead>
                  <tr>
                    <th>Empleado</th>
                    <th>Documento</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($documentos as $doc): ?>
                  <tr>
                    <td><?= htmlspecialchars($doc['empleado_nombre']) ?><br><small><?= htmlspecialchars($doc['email']) ?></small></td>
                    <td><?= htmlspecialchars($doc['nombre_original']) ?></td>
                    <td>
                        <span class="estado estado-<?= strtolower(htmlspecialchars($doc['estado'] ?? '')) ?>">
                            <?= htmlspecialchars(strtoupper($doc['estado'] ?? 'N/A')) ?>
                        </span>
                    </td>
                    <td><?= $doc['fecha_subida'] ?></td>
                    <td style="display: flex; gap: 10px;">
                      <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=view" target="_blank" class="btn-view"><i class="fas fa-eye"></i> Ver</a>
                      <a href="../php/ver_documento.php?id=<?= $doc['id'] ?>&action=download" class="btn-download"><i class="fas fa-download"></i> Descargar</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p>No hay documentos subidos en el sistema.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('documentosTable');
    if (table) {
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                // Busca en todas las celdas (excepto la de acciones)
                for (let j = 0; j < cells.length - 1; j++) {
                    if (cells[j] && cells[j].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                if (found) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        });
    }
});
</script>
<?php require_once '../includes/footer.php'; ?>