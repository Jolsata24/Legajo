<?php
session_start();
require '../php/db.php';
require '../vendor/autoload.php'; // Carga Dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}

$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$clave_sin_hash = isset($_GET['clave']) ? $_GET['clave'] : '';

if ($id_usuario <= 0) {
    die("Usuario no especificado.");
}

// Obtenemos los datos del usuario
try {
    $stmt = $pdo->prepare("SELECT u.nombre, u.email, u.rol, a.nombre as area_nombre FROM usuarios u LEFT JOIN areas a ON u.id_area = a.id WHERE u.id = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();
} catch (PDOException $e) {
    die("Error al buscar el usuario.");
}

// Convertir logo a Base64
$path_logo = '../img/dremhlogo.png';
$type_logo = pathinfo($path_logo, PATHINFO_EXTENSION);
$data_logo = file_get_contents($path_logo);
$logo_base64 = 'data:image/' . $type_logo . ';base64,' . base64_encode($data_logo);

// Contenido HTML del PDF
$html_pdf = "
<html>
<head><style>/* ... (Tus estilos para el PDF aquí) ... */</style></head>
<body>
    <div class='header'>
        <img src='{$logo_base64}' style='width:100px;'>
        <h1>Credenciales de Acceso - Sistema de Legajos</h1>
    </div>
    <p>Estimado/a ".htmlspecialchars($usuario['nombre']).",</p>
    <p>Se ha creado una cuenta para usted en el sistema. Sus datos son:</p>
    <table border='1' cellpadding='10' cellspacing='0' width='100%'>
        <tr>
            <td style='background-color:#f2f2f2;'><strong>Usuario (Email)</strong></td>
            <td>".htmlspecialchars($usuario['email'])."</td>
        </tr>
        <tr>
            <td style='background-color:#f2f2f2;'><strong>Contraseña Temporal</strong></td>
            <td>".htmlspecialchars($clave_sin_hash)."</td>
        </tr>
        <tr>
            <td style='background-color:#f2f2f2;'><strong>Rol</strong></td>
            <td>".htmlspecialchars($usuario['rol'])."</td>
        </tr>
        <tr>
            <td style='background-color:#f2f2f2;'><strong>Área</strong></td>
            <td>".htmlspecialchars($usuario['area_nombre'] ?? 'N/A')."</td>
        </tr>
    </table>
    <p>Se recomienda cambiar la contraseña en el primer inicio de sesión.</p>
</body>
</html>
";

// Generar y descargar el PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html_pdf);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Forzar la descarga
$dompdf->stream("credenciales-" . strtolower(str_replace(' ', '_', $usuario['nombre'])) . ".pdf", ["Attachment" => true]);
exit;
?>