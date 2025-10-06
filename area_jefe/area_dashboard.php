<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'jefe_area') {
    header("Location: ../into/login.html");
    exit;
}

// Métricas para el Jefe de Área
$id_area = $_SESSION['id_area'] ?? 0;
$documentos_en_area = 0;
$empleados_en_area = 0;

try {
    $stmt_docs = $pdo->prepare("SELECT COUNT(*) FROM documentos WHERE id_area_destino = ?");
    $stmt_docs->execute([$id_area]);
    $documentos_en_area = $stmt_docs->fetchColumn();

    $stmt_emps = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_area = ? AND rol = 'empleado'");
    $stmt_emps->execute([$id_area]);
    $empleados_en_area = $stmt_emps->fetchColumn();
} catch (PDOException $e) {
    // Manejar error
}

$page_title = "Dashboard - Jefe de Área";
require_once '../includes/header_jefe.php';
require_once '../includes/sidebar_jefe.php';
?>

<div class="main">
    <header class="topbar">
      <h1><i class="fas fa-tachometer-alt"></i> Dashboard de Jefe de Área</h1>
    </header>

    <main class="content">
        <div class="cards">
            <div class="card">
                <h3>Documentos en tu Área</h3>
                <p><?= $documentos_en_area ?></p>
            </div>
            <div class="card">
                <h3>Empleados en tu Área</h3>
                <p><?= $empleados_en_area ?></p>
            </div>
        </div>

        <div class="card">
            <h3>Acciones Rápidas</h3>
            <div style="margin-top: 20px; text-align: left;">
                <a href="area_documentos.php" style="text-decoration: none; background: #007bff; color: white; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-folder-open"></i> Revisar Documentos del Área
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>