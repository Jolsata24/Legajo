<?php
$foto_path = '../img/user2.png';
if (!empty($_SESSION['foto'])) {
    $ruta_foto_usuario = '../uploads/usuarios/' . $_SESSION['foto'];
    if (file_exists($ruta_foto_usuario)) {
        $foto_path = $ruta_foto_usuario;
    }
}
?>
<aside class="sidebar">
    <div class="brand">
      <h2>Panel de Secretaría</h2>
    </div>
    <div class="user-info">
      <img src="<?= htmlspecialchars($foto_path) ?>" alt="Foto del Usuario">
      <h4><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h4>
      <p><?= htmlspecialchars(ucfirst($_SESSION['rol'] ?? '')) ?></p>
    </div>
    <nav class="menu">
  <a href="secretaria_dashboard.php"><i class="fas fa-home"></i> Inicio</a>
  <a href="secretaria_documentos.php"><i class="fas fa-inbox"></i> Bandeja de Entrada</a>
  <a href="mi_legajo.php"><i class="fas fa-folder-open"></i> Mi Legajo</a>

  <a href="editar_perfil.php"><i class="fas fa-user-edit"></i> Editar Perfil</a>
  
  <a href="../php/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
</nav>
</aside>