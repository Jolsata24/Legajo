<?php
session_start();
require 'db.php'; 
require_once 'funciones.php'; // --- CORRECCIÓN 1: Importante para que funcione el log ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');

    if (empty($email) || empty($clave)) {
        die("Por favor complete todos los campos.");
    }

    try {
        $stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol, id_area, foto 
                               FROM usuarios 
                               WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificamos contraseña PRIMERO
        if ($user && password_verify($clave, $user['password_hash'])) {
            
            // --- CORRECCIÓN 2: El log se guarda SOLO si la contraseña es correcta ---
            registrar_auditoria($pdo, $user['id'], 'LOGIN_EXITOSO', 'Ingreso al sistema');

            // Guardar datos en sesión
            $_SESSION['id']       = $user['id'];
            $_SESSION['nombre']   = $user['nombre'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['rol']      = $user['rol'];
            $_SESSION['id_area']  = $user['id_area'];
            $_SESSION['foto']     = $user['foto']; 

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
                case 'sistemas': 
                    header("Location: ../sistemas/dashboard_auditoria.php");
                    break;
                default:
                    echo "Rol desconocido o inactivo.";
            }
            exit;
        } else {
            // (Opcional) Aquí podrías registrar un 'LOGIN_FALLIDO' si quisieras mayor seguridad
            // registrar_auditoria($pdo, null, 'LOGIN_FALLIDO', 'Intento con email: '.$email);
            
            echo "Usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
}
?>