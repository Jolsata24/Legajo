<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header('Location: login.html');
    exit;
}

switch ($_SESSION['rol']) {
    case 'admin':
        header('Location: ../admin/admin_dashboard.php');
        break;
    case 'rrhh':
        header('Location: ../rrhh/rrhh_dashboard.php');
        break;
    case 'secretaria':
        header('Location: ../secretaria/secretaria_dashboard.php');
        break;
    case 'jefe_area':
        header('Location: ../area_jefe/area_dashboard.php');
        break;
    case 'empleado':
        header('Location: ../empleado/empleado_dashboard.php');
        break;
    default:
        echo "Rol desconocido.";
        session_destroy();
        break;
}
exit;

