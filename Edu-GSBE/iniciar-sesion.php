<?php
session_start();

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Administradores autorizados
$admin_autorizados = [
    'laurada.diazgu@ecci.edu.co' => 'Admin2026*',
    'andresfe.penaor@ecci.edu.co' => 'Admin2026*'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1. Verificación directa de Administradores Autorizados
    if (array_key_exists($email, $admin_autorizados) && $admin_autorizados[$email] === $password) {
        $_SESSION['usuario'] = $email;
        $_SESSION['rol']     = 'administrador';

        echo "<script>
                alert('¡Bienvenido Administrador ($email)!');
                window.location.href = 'panel-admin.php';
              </script>";
        exit();
    }

    // 2. Verificación para estudiantes en la Base de Datos MySQL
    if (!$conexion->connect_error) {
        $sql = "SELECT id, nombre, email, password, rol FROM usuarios WHERE email = '$email'";
        $resultado = $conexion->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            $user = $resultado->fetch_assoc();

            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['nombre']     = $user['nombre'];
                $_SESSION['rol']        = 'estudiante';

                echo "<script>
                        alert('¡Bienvenido(a), " . $user['nombre'] . "!');
                        window.location.href = 'panel-estudiante.php';
                      </script>";
                exit();
            }
        }
    }

    // Si las credenciales no coinciden
    echo "<script>
            alert('Correo o contraseña incorrectos.');
            window.history.back();
          </script>";
}
?>