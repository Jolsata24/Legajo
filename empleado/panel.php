<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../html/login.html");
    exit;
}

$rol = $_SESSION['rol'];
$id_area = $_SESSION['id_area'];

if ($rol === 'jefe_area') {
    // Solo documentos enviados a SU área
    $stmt = $conn->prepare("SELECT d.*, u.nombre FROM documentos d 
                            JOIN usuarios u ON d.id_usuario = u.id
                            JOIN areas a ON d.id_area_destino = a.id
                            WHERE d.id_area_destino = ?");
    $stmt->bind_param("i", $id_area);
} elseif ($rol === 'admin' || $rol === 'rrhh') {
    // Ven todos los documentos
    $stmt = $conn->prepare("SELECT d.*, u.nombre, a.nombre_area FROM documentos d 
                            JOIN usuarios u ON d.id_usuario = u.id
                            JOIN areas a ON d.id_area_destino = a.id");
} else {
    // Empleado solo ve sus documentos
    $stmt = $conn->prepare("SELECT d.*, a.nombre_area FROM documentos d
                            JOIN areas a ON d.id_area_destino = a.id
                            WHERE d.id_usuario = ?");
    $stmt->bind_param("i", $_SESSION['id']);
}

$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Documentos</h2>";
echo "<table border='1'><tr><th>Empleado</th><th>Documento</th><th>Área destino</th><th>Fecha</th><th>Acción</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['nombre'] ?? 'Yo mismo') . "</td>";
    echo "<td>" . htmlspecialchars($row['nombre_original']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nombre_area'] ?? '') . "</td>";
    echo "<td>" . $row['fecha_subida'] . "</td>";
    echo "<td><a href='../uploads/" . urlencode($row['nombre_guardado']) . "' download>Descargar</a></td>";
    echo "</tr>";
}
echo "</table>";
?>
