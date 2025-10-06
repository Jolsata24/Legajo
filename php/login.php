<?php
session_start();
require 'db.php'; // Asegúrate que aquí inicializas $pdo correctamente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $email = trim($_POST['usuario'] ?? '');   // el input "usuario" será el email
    $clave = trim($_POST['clave'] ?? '');

    if ($email === '' || $clave === '') {
        echo "Por favor complete todos los campos.";
        exit;
    }

    try {
        // Buscar usuario por email (agregamos id_area)
        $stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol, id_area 
                               FROM usuarios 
                               WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($clave, $user['password_hash'])) {
            // Guardar sesión
            $_SESSION['id']       = $user['id'];
            $_SESSION['nombre']   = $user['nombre'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['rol']      = $user['rol'];
            $_SESSION['id_area']  = $user['id_area'];  // ✅ IMPORTANTE

            // Redirigir según el rol
            switch ($user['rol']) {
                case 'admin':
                    header("Location: ../admin/admin_dashboard.php");
                    break;
                case 'secretaria':
                    header("Location: ../secretaria/secretaria_dashboard.php");
                    break;
                case 'rrhh':
                    header("Location: ../rrhh/rrhh_dashboard.php");
                    break;
                case 'jefe_area':
                    header("Location: ../area_jefe/area_dashboard.php");
                    break;
                case 'empleado':
                    header("Location: ../empleado/empleado_dashboard.php");
                    break;
                default:
                    echo "Rol desconocido.";
            }
            exit;
        } else {
            echo "Usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
} else {
    echo "Método no permitido.";
}
