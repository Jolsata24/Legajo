<?php
session_start();
require 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');

    if (empty($email) || empty($clave)) {
        die("Por favor complete todos los campos.");
    }

    try {
        // --- CORRECCIÓN AQUÍ: Añadimos la columna 'foto' a la consulta ---
        $stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol, id_area, foto 
                               FROM usuarios 
                               WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($clave, $user['password_hash'])) {
            // Guardar todos los datos en la sesión
            $_SESSION['id']       = $user['id'];
            $_SESSION['nombre']   = $user['nombre'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['rol']      = $user['rol'];
            $_SESSION['id_area']  = $user['id_area'];
            
            // --- CORRECCIÓN AQUÍ: Guardamos el nombre del archivo de la foto ---
            $_SESSION['foto']  = $user['foto']; // Esto ahora sí tendrá un valor

            // Redirigir según el rol (tu código existente)
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
}
?>