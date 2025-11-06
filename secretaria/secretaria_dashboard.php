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
<style>
/* Estilos para la Campana de Notificaciones */
.notifications {
    position: relative; /* Clave para el menú desplegable */
    margin-right: 15px;
}

#notification-bell {
    font-size: 20px;
    color: #555;
    text-decoration: none;
}

.notification-count {
    position: absolute;
    top: -5px;
    right: -10px;
    background: #dc3545; /* Rojo de alerta */
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 5px;
    border-radius: 50%;
}

/* El Menú Desplegable (oculto por defecto) */
.notification-dropdown {
    display: none; /* Oculto por defecto */
    position: absolute;
    top: 40px;
    right: 0;
    width: 320px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    z-index: 100;
    border: 1px solid #eee;
}

/* Clase para mostrar el menú con JS */
.notification-dropdown.show {
    display: block; 
}

.notification-dropdown .dropdown-header {
    padding: 12px 15px;
    font-weight: 600;
    border-bottom: 1px solid #f0f0f0;
}

.notification-dropdown .dropdown-body a {
    display: block;
    padding: 12px 15px;
    text-decoration: none;
    color: #333;
    border-bottom: 1px solid #f0f0f0;
}
.notification-dropdown .dropdown-body a:hover {
    background: #f8f9fa;
}
.notification-dropdown .dropdown-body a p {
    font-size: 13px;
    margin: 0;
    color: #222;
}
.notification-dropdown .dropdown-body a small {
    font-size: 11px;
    color: #777;
}

.notification-dropdown .dropdown-footer {
    padding: 10px;
    text-align: center;
}
.notification-dropdown .dropdown-footer a {
    font-size: 13px;
    font-weight: 500;
    color: #007bff;
}</style>

<div class="main">
    <header class="topbar">
        <h1><i class="fas fa-tachometer-alt"></i> Inicio</h1>
        
        <div class="top-actions">
            <span style="margin-left: 20px;"><i class="fas fa-calendar-alt"></i> <?= date("d/m/Y") ?></span>
        </div>

        <a href="../php/logout.php" class="topbar-logout-btn">
          <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
      </a>

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