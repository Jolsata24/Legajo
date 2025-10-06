<?php
session_start();
require '../php/db.php';

// ✅ Verificamos sesión y roles permitidos
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['admin', 'rrhh', 'jefe_area'])) {
    die("Acceso denegado");
}

$empleado_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$seccion_id  = isset($_GET['seccion']) ? (int) $_GET['seccion'] : 0;

if ($empleado_id <= 0 || $seccion_id <= 0) {
    die("Parámetros inválidos.");
}

// ✅ Consultar datos del empleado
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$empleado_id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ Consultar datos de la sección
$stmt = $pdo->prepare("SELECT nombre FROM secciones_legajo WHERE id = ?");
$stmt->execute([$seccion_id]);
$seccion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado || !$seccion) {
    die("Empleado o sección no encontrados.");
}

// ✅ Consultar documentos de esa sección
$stmt = $pdo->prepare("SELECT id, nombre_original, nombre_guardado, tipo, fecha_subida
                       FROM documentos
                       WHERE id_usuario = ? AND id_seccion = ?
                       ORDER BY fecha_subida DESC");
$stmt->execute([$empleado_id, $seccion_id]);
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sección - <?php echo htmlspecialchars($seccion['nombre']); ?></title>
</head>
<body>
    <h2>📂 Documentos de <?php echo htmlspecialchars($empleado['nombre']); ?> en <?php echo htmlspecialchars($seccion['nombre']); ?></h2>
    <nav>
        <a href="ver_legajo.php?id=<?php echo $empleado_id; ?>">⬅ Volver al Legajo</a>
    </nav>
    <hr>

    <?php if ($documentos): ?>
        <ul>
            <?php foreach ($documentos as $doc): ?>
                <li>
                    <b><?php echo htmlspecialchars($doc['nombre_original']); ?></b><br>
                    <small>📅 <?php echo $doc['fecha_subida']; ?> | Tipo: <?php echo htmlspecialchars($doc['tipo']); ?></small><br>
                    <a href="../uploads/<?php echo htmlspecialchars($doc['nombre_guardado']); ?>" target="_blank">📄 Ver Documento</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No hay documentos en esta sección.</p>
    <?php endif; ?>
</body>
</html>
