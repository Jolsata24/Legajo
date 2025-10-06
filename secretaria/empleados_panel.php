<?php
session_start();
require '../php/db.php';

// Verificar sesión
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['jefe_area', 'rrhh', 'admin','secretaria'])) {
    header("Location: ../into/login.html");
    exit;
}

$usuario_id = $_SESSION['id'];
$rol = $_SESSION['rol'];
$id_area = $_SESSION['id_area'] ?? null;

try {
    if ($rol === 'jefe_area' && $id_area) {
        // Jefe de área solo ve a empleados de su área
        $stmt = $pdo->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id_area = ? AND rol = 'empleado'");
        $stmt->execute([$id_area]);
    } else {
        // RRHH y Admin ven todos los empleados
        $stmt = $pdo->query("SELECT id, nombre, email, rol FROM usuarios WHERE rol = 'empleado'");
    }
    $empleados = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Empleados</title>
    <style>
        .grid { display: flex; flex-wrap: wrap; gap: 15px; }
        .card {
            border: 1px solid #ccc; padding: 15px;
            border-radius: 10px; width: 200px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
        }
        .card h3 { margin: 0; font-size: 18px; }
        .card p { margin: 5px 0; font-size: 14px; }
        .card a { display: inline-block; margin-top: 10px; padding: 5px 10px; background: #007BFF; color: #fff; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>👥 Panel de Empleados</h1>
    <nav>
        <a href="../php/logout.php">Cerrar sesión</a>
    </nav>
    <hr>

    <div class="grid">
        <?php foreach ($empleados as $emp): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($emp['nombre']); ?></h3>
                <p><b>Email:</b> <?php echo htmlspecialchars($emp['email']); ?></p>
                <a href="../panel_de_jefe/ver_empleado.php?id=<?php echo $emp['id']; ?>">Ver Detalle</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
