<?php
session_start();
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id = intval($_GET['id']);
    $estado = $conexion->real_escape_string($_GET['estado']); // 'aprobado' o 'rechazado'

    // Actualizar el estado en la tabla de solicitudes
    $conexion->query("UPDATE solicitudes SET estado = '$estado' WHERE id = $id");
}

// Redireccionar de vuelta a la página de evaluación de solicitudes
header("Location: evaluar-solicitudes.php");
exit();
?>