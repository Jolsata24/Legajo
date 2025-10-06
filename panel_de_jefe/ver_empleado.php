<?php
session_start();
require '../php/db.php';

// ✅ Verificamos sesión y roles permitidos
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['admin', 'rrhh', 'jefe_area'])) {
    die("Acceso denegado");
}

// ✅ Obtenemos el id del empleado desde la URL
$empleado_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($empleado_id <= 0) {
    die("Empleado no válido.");
}

// ✅ Consultamos los datos del empleado
$stmt = $pdo->prepare("SELECT u.id, u.nombre, u.email, u.rol, a.nombre AS area
                       FROM usuarios u
                       LEFT JOIN areas a ON u.id_area = a.id
                       WHERE u.id = ?");
$stmt->execute([$empleado_id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    die("Empleado no encontrado.");
}

// ✅ Consultamos todas las secciones
$stmt = $pdo->query("SELECT id, nombre FROM secciones_legajo ORDER BY id ASC");
$secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Consultamos documentos enviados a áreas
$stmt = $pdo->prepare("SELECT d.id, d.nombre_original, d.nombre_guardado, d.tipo, d.fecha_subida, a.nombre AS area_destino
                       FROM documentos d
                       INNER JOIN areas a ON d.id_area_destino = a.id
                       WHERE d.id_usuario = ? AND d.id_area_destino IS NOT NULL
                       ORDER BY d.fecha_subida DESC");
$stmt->execute([$empleado_id]);
$docs_areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Legajo - <?php echo htmlspecialchars($empleado['nombre']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { border: 1px solid #ccc; border-radius: 10px; padding: 15px; margin-bottom: 20px; }
        .card h3 { margin-top: 0; }
        .seccion-btn { display: block; margin: 8px 0; padding: 8px; border: 1px solid #666; border-radius: 5px; text-decoration: none; color: black; background: #f4f4f4; }
        .seccion-btn:hover { background: #ddd; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>📂 Legajo de <?php echo htmlspecialchars($empleado['nombre']); ?></h2>

    <!-- Datos del empleado -->
    <div class="card">
        <h3>👤 Datos del Empleado</h3>
        <p><b>Nombre:</b> <?php echo htmlspecialchars($empleado['nombre']); ?></p>
        <p><b>Email:</b> <?php echo htmlspecialchars($empleado['email']); ?></p>
        <p><b>Rol:</b> <?php echo htmlspecialchars($empleado['rol']); ?></p>
        <p><b>Área:</b> <?php echo htmlspecialchars($empleado['area']); ?></p>
    </div>

    <!-- Documentos Personales organizados por secciones -->
    <div class="card">
        <h3>📑 Documentos Personales (por Secciones)</h3>
        <?php if ($secciones): ?>
            <?php foreach ($secciones as $sec): ?>
                <a class="seccion-btn" href="ver_seccion_legajo.php?id=<?php echo $empleado_id; ?>&seccion=<?php echo $sec['id']; ?>">
                    📌 <?php echo htmlspecialchars($sec['nombre']); ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay secciones configuradas.</p>
        <?php endif; ?>
    </div>

    <!-- Documentos enviados a áreas -->
    <div class="card">
        <h3>📤 Documentos enviados a otras áreas</h3>
        <?php if ($docs_areas): ?>
            <ul>
                <?php foreach ($docs_areas as $doc): ?>
                    <li>
                        <b><?php echo htmlspecialchars($doc['nombre_original']); ?></b><br>
                        <small>📅 <?php echo $doc['fecha_subida']; ?> | Tipo: <?php echo htmlspecialchars($doc['tipo']); ?> | Área destino: <?php echo htmlspecialchars($doc['area_destino']); ?></small><br>
                        <a href="../uploads/<?php echo htmlspecialchars($doc['nombre_guardado']); ?>" target="_blank">📄 Ver Documento</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No ha enviado documentos a otras áreas.</p>
        <?php endif; ?>
    </div>

    <p><a href="panel_supervision.php">⬅ Volver al Panel</a></p>
</body>
</html>
