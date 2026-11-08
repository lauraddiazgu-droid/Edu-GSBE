<?php
session_start();
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = intval($_GET['id']);
    $tipo = $_GET['tipo'];

    if ($tipo == 'beca') {
        $conexion->query("DELETE FROM becas WHERE id = $id");
    } elseif ($tipo == 'prestamo') {
        $conexion->query("DELETE FROM prestamos WHERE id = $id");
    }
}

header("Location: gestionar-becas.php");
exit();
?>