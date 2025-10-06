<?php
session_start();
require '../php/db.php';

// Seguridad y obtención de datos (métricas y notificaciones)
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'secretaria') {
    header("Location: ../into/login.html");
    exit;
}

$pendientes = 0;
try {
    $pendientes = $pdo->query("SELECT COUNT(*) FROM documentos WHERE estado = 'pendiente'")->fetchColumn();
    // (Aquí iría tu código para buscar notificaciones si lo tienes)
} catch (PDOException $e) { /*...*/ }

$page_title = "Dashboard - Secretaría";
require_once '../includes/header_secretaria.php';
require_once '../includes/sidebar_secretaria.php';
?>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-tachometer-alt"></i> Inicio</h1>
        <div class="top-actions">
            <span style="margin-left: 20px;"><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>
    </header>

    <main class="content">
        <div class="cards">
            <div class="card">
                <h3>Documentos Pendientes</h3>
                <p style="color: #ffc107;"><?= $pendientes ?></p>
            </div>
        </div>

        <div class="card">
            <h3>Acciones Principales</h3>
            <p style="text-align: left; font-size: 16px; font-weight: 400;">
                Bienvenida al panel. Tu tarea principal es gestionar los documentos entrantes.
            </p>
            <div style="margin-top: 20px; text-align: left;">
                <a href="secretaria_documentos.php" style="text-decoration: none; background: #007bff; color: white; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-inbox"></i> Ir a Bandeja de Entrada (<?= $pendientes ?>)
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>