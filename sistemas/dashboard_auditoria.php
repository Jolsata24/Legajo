<?php
session_start();
require '../php/db.php';

// Verificar Permiso (Solo Sistemas o Admin)
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['sistemas', 'admin'])) {
    header("Location: ../into/login.html");
    exit;
}

// Filtros básicos
$filtro_rol = $_GET['rol'] ?? '';
$filtro_accion = $_GET['accion'] ?? '';

// Construir consulta
$sql = "SELECT * FROM auditoria_logs WHERE 1=1";
$params = [];

if ($filtro_rol) {
    $sql .= " AND rol_usuario = ?";
    $params[] = $filtro_rol;
}
if ($filtro_accion) {
    $sql .= " AND accion LIKE ?";
    $params[] = "%$filtro_accion%";
}

$sql .= " ORDER BY fecha DESC LIMIT 100"; // Últimos 100 eventos

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$page_title = "Panel de Auditoría y Seguridad";
// Puedes reutilizar admin_dashboard.css o crear uno simple
$extra_css = "../style/admin_dashboard.css"; 

// Nota: Deberías crear un header_sistemas.php similar al de admin, 
// o usar el de admin pero ocultando cosas. Por ahora usaré html básico.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/global.css">
    <link rel="stylesheet" href="../style/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Estilos específicos para la tabla de logs */
        .log-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .log-table th, .log-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .log-table th { background: #f8f9fa; color: #555; }
        .log-tag { padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 11px; }
        .tag-login { background: #d1e7dd; color: #0f5132; }
        .tag-error { background: #f8d7da; color: #842029; }
        .tag-info { background: #cff4fc; color: #055160; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand">
            <h2><i class="fas fa-shield-alt"></i> SISTEMAS</h2>
        </div>
        <div class="user-info">
            <h4><?= htmlspecialchars($_SESSION['nombre']) ?></h4>
            <p>Auditor</p>
        </div>
        <div class="menu">
            <a href="dashboard_auditoria.php" class="active"><i class="fas fa-list-alt"></i> Logs de Auditoría</a>
            <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="main">
        <header class="topbar">
            <h1>Auditoría del Sistema</h1>
            <span><i class="fas fa-server"></i> Monitoreo Activo</span>
        </header>

        <div class="card-container" style="margin-bottom: 20px;">
            <form method="GET" style="display: flex; gap: 15px;">
                <select name="rol" class="form-select" style="padding: 8px;">
                    <option value="">Todos los Roles</option>
                    <option value="admin">Admin</option>
                    <option value="empleado">Empleado</option>
                </select>
                <input type="text" name="accion" placeholder="Buscar acción (ej: LOGIN)" style="padding: 8px;">
                <button type="submit" class="btn-quick-action" style="margin:0;">Filtrar</button>
            </form>
        </div>

        <div class="card-container">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acción</th>
                        <th>Detalle</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): 
                        $tagClass = 'tag-info';
                        if (strpos($log['accion'], 'LOGIN') !== false) $tagClass = 'tag-login';
                        if (strpos($log['accion'], 'ERROR') !== false || strpos($log['accion'], 'DELETE') !== false) $tagClass = 'tag-error';
                    ?>
                    <tr>
                        <td><?= $log['fecha'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($log['usuario_nombre']) ?></strong>
                            <br><small style="color:#888;">ID: <?= $log['id_usuario'] ?></small>
                        </td>
                        <td><?= strtoupper($log['rol_usuario']) ?></td>
                        <td><span class="log-tag <?= $tagClass ?>"><?= $log['accion'] ?></span></td>
                        <td><?= htmlspecialchars($log['detalle']) ?></td>
                        <td style="font-family: monospace;"><?= $log['ip_origen'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>