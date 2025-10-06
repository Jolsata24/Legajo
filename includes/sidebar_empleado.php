<?php
// --- LÓGICA MEJORADA PARA LA FOTO ---

// 1. Ruta por defecto
$foto_a_mostrar = '../img/user2.png'; 

// 2. Comprobar si hay una foto en la sesión
if (!empty($_SESSION['foto'])) {
    // 3. Construir la ruta completa al archivo del usuario
    $ruta_foto_usuario = '../uploads/usuarios/' . $_SESSION['foto'];
    
    // 4. ¡Importante! Verificar si el archivo realmente existe en el servidor
    if (file_exists($ruta_foto_usuario)) {
        $foto_a_mostrar = $ruta_foto_usuario; // Si existe, usamos esta ruta
    }
}
?>
<aside class="sidebar">
    <div class="brand">
      <h2>Panel de Empleado</h2>
    </div>
    <div class="user-info">
      <img src="<?= htmlspecialchars($foto_a_mostrar) ?>" alt="Foto del Usuario">
      
      <h4><?= htmlspecialchars($_SESSION['nombre'] ?? 'Empleado') ?></h4>
      <p><?= htmlspecialchars(ucfirst($_SESSION['rol'] ?? '')) ?></p>
    </div>
    <nav class="menu">
  <a href="empleado_dashboard.php"><i class="fas fa-home"></i> Inicio</a>
  <a href="mi_legajo.php"><i class="fas fa-folder-open"></i> Mi Legajo</a>
  <a href="enviar_documento.php"><i class="fas fa-paper-plane"></i> Enviar Documento</a>
  
  <a href="documentos_enviados.php"><i class="fas fa-history"></i> Mis Envíos</a>
  
  <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
</nav>
</aside>