<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $beca_id    = intval($_POST['beca_id']);
    $motivo     = $conexion->real_escape_string($_POST['motivo']);
    $codigo     = "SOL-" . rand(100000, 999999);

    // Guardar solicitud en MySQL
    $sql = "INSERT INTO solicitudes (codigo, usuario_id, beca_id, motivo, estado, fecha_solicitud) 
            VALUES ('$codigo', '$usuario_id', '$beca_id', '$motivo', 'Pendiente', NOW())";

    if ($conexion->query($sql) === TRUE) {
        echo "<script>
                alert('¡Solicitud enviada con éxito! Código asignado: $codigo. Tu postulación está en revisión por el Administrador.');
                window.location.href = 'estado-solicitud.php';
              </script>";
    } else {
        echo "Error al registrar solicitud: " . $conexion->error;
    }

    $conexion->close();
}
?>