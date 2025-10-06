<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['jefe_area', 'rrhh', 'admin'])) {
    header("Location: ../into/login.html");
    exit;
}

$empleado_id = $_GET['id'] ?? null;

if (!$empleado_id) {
    die("Empleado no especificado.");
}

try {
    // Datos del empleado
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, a.nombre AS area
        FROM usuarios u
        LEFT JOIN areas a ON u.id_area = a.id
        WHERE u.id = ?
    ");
    $stmt->execute([$empleado_id]);
    $empleado = $stmt->fetch();

    if (!$empleado) {
        die("Empleado no encontrado.");
    }

    // Documentos personales
    $stmt = $pdo->prepare("
        SELECT id, nombre_original, tipo, fecha_subida
        FROM documentos
        WHERE id_usuario = ? AND id_area_destino IS NULL
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$empleado_id]);
    $documentos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Empleado</title>
</head>
<body>
    <h1>👤 Detalle del Empleado</h1>
    <nav>
        <a href="empleados_panel.php">Volver</a> | 
        <a href="../php/logout.php">Cerrar sesión</a>
    </nav>
    <hr>

    <h2>📌 Datos Personales</h2>
    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($empleado['nombre']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($empleado['email']); ?></p>
    <p><strong>Rol:</strong> <?php echo htmlspecialchars($empleado['rol']); ?></p>
    <p><strong>Área:</strong> <?php echo htmlspecialchars($empleado['area'] ?? 'No asignada'); ?></p>

    <h2>📂 Documentos Personales</h2>
    <?php if (count($documentos) > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($documentos as $doc): ?>
                <tr>
                    <td><?php echo $doc['id']; ?></td>
                    <td><?php echo htmlspecialchars($doc['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($doc['nombre_original']); ?></td>
                    <td><?php echo $doc['fecha_subida']; ?></td>
                    <td><a href="../php/ver_documento.php?id=<?php echo $doc['id']; ?>">📥 Descargar</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No tiene documentos personales subidos.</p>
    <?php endif; ?>
</body>
</html>
