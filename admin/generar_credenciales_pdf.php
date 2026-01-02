<?php
session_start();
require '../php/db.php';
require '../vendor/autoload.php'; // Asegúrate que la ruta a vendor sea correcta

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}

$id_usuario = $_GET['id'] ?? 0;
$pass_temp  = $_GET['pass'] ?? '*****';

// Consulta de datos
try {
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, u.rol, a.nombre as area 
        FROM usuarios u 
        LEFT JOIN areas a ON u.id_area = a.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();
} catch (Exception $e) {
    die("Error de datos.");
}

// Logo en Base64 para evitar problemas de rutas en DomPDF
$path_logo = '../img/dremhlogo.png';
$logo_base64 = '';
if (file_exists($path_logo)) {
    $type = pathinfo($path_logo, PATHINFO_EXTENSION);
    $data = file_get_contents($path_logo);
    $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// HTML del PDF
$html = '
<html>
<head>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #0d6efd; padding-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; color: #0d6efd; margin-top: 10px; }
        .content { margin: 0 40px; }
        .box { background: #f4f6f9; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .item { margin-bottom: 10px; font-size: 14px; }
        .label { font-weight: bold; width: 150px; display: inline-block; color: #555; }
        .value { font-family: monospace; font-size: 16px; color: #000; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="header">
        '.($logo_base64 ? '<img src="'.$logo_base64.'" width="150">' : '<h2>DREMH</h2>').'
        <div class="title">Credenciales de Acceso</div>
    </div>

    <div class="content">
        <p>Estimado/a <strong>'.htmlspecialchars($usuario['nombre']).'</strong>,</p>
        <p>Bienvenido/a al Sistema de Gestión de Legajos Digitales. A continuación se detallan sus credenciales de acceso personal:</p>

        <div class="box">
            <div class="item">
                <span class="label">Rol Asignado:</span>
                '.ucfirst($usuario['rol']).'
            </div>
            <div class="item">
                <span class="label">Área / Dpto:</span>
                '.htmlspecialchars($usuario['area'] ?? 'General').'
            </div>
            <hr style="border: 0; border-top: 1px dashed #ccc; margin: 15px 0;">
            <div class="item">
                <span class="label">Usuario (Email):</span>
                <span class="value">'.htmlspecialchars($usuario['email']).'</span>
            </div>
            <div class="item">
                <span class="label">Contraseña:</span>
                <span class="value">'.htmlspecialchars($pass_temp).'</span>
            </div>
        </div>

        <p style="font-size: 12px; color: #dc3545; margin-top: 20px;">
            <strong>Importante:</strong> Por motivos de seguridad, se le recomienda cambiar esta contraseña temporal inmediatamente después de su primer inicio de sesión.
        </p>
    </div>

    <div class="footer">
        Generado automáticamente por el Sistema de Legajos DREMH - '.date("d/m/Y H:i").'
    </div>
</body>
</html>';

// Generar PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'landscape'); // A5 Horizontal es ideal para fichas
$dompdf->render();

$dompdf->stream("Credenciales_".str_replace(" ", "_", $usuario['nombre']).".pdf", array("Attachment" => true));
exit;
?>