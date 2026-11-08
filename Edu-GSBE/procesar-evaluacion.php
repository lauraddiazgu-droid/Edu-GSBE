<?php
session_start();
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if (isset($_GET['id']) && isset($_GET['accion'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    // Definir el nuevo estado según la acción
    $nuevo_estado = ($accion === 'aprobar') ? 'aprobada' : 'rechazada';

    // Actualizar el estado de la solicitud en la base de datos
    $stmt = $conexion->prepare("UPDATE solicitudes SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id);
    $stmt->execute();
}

// Redireccionar de vuelta a la lista de solicitudes
header("Location: evaluar-solicitudes.php");
exit();
?>