<?php
session_start();

// Conexión a la base de datos
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion   = new mysqli($servidor, $usuario, $password, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener ID del estudiante logueado (por defecto 1 si no hay sesión activa para pruebas)
$usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 1;

// Consulta SQL corregida utilizando únicamente las columnas reales de la tabla solicitudes
$sql = "SELECT id, tipo_beca, estado, fecha_solicitud FROM solicitudes WHERE usuario_id = $usuario_id ORDER BY fecha_solicitud DESC";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Solicitud - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; padding: 12px 20px; }
        .logo { height: 40px; }
        .pagina { max-width: 900px; margin: 30px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        .volver { display: inline-block; margin-bottom: 15px; color: #2a5fb0; text-decoration: none; font-size: 13px; font-weight: 600; }
        .volver:hover { text-decoration: underline; }
        h1 { color: #14325c; font-size: 24px; margin-bottom: 6px; font-weight: 700; }
        .intro { color: #64748b; font-size: 13px; margin-bottom: 20px; }

        .tarjeta-tabla { background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        th { background-color: #f8fafc; color: #475569; padding: 12px; border-bottom: 2px solid #e2e8f0; font-weight: bold; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .badge-pendiente { background-color: #fef3c7; color: #d97706; }
        .badge-aprobado { background-color: #dcfce7; color: #16a34a; }
        .badge-rechazado { background-color: #fee2e2; color: #dc2626; }

        .sin-datos { text-align: center; padding: 30px; color: #64748b; font-size: 14px; }
        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 20px; text-align: center; font-size: 12px; margin-top: 40px; }
    </style>
</head>
<body>
    <header>
        <a href="panel-estudiante.php"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
    </header>

    <main class="pagina">
        <a href="panel-estudiante.php" class="volver">&#8249; Volver al Panel Estudiantil</a>
        
        <h1>Estado de tus Solicitudes</h1>
        <p class="intro">Aquí puedes consultar el seguimiento en tiempo real de las convocatorias a las que te has postulado.</p>

        <div class="tarjeta-tabla">
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Solicitud</th>
                            <th>Tipo de Beca / Convocatoria</th>
                            <th>Fecha de Postulación</th>
                            <th>Estado Actual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($fila['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($fila['tipo_beca']); ?></strong></td>
                                <td><?php echo htmlspecialchars($fila['fecha_solicitud']); ?></td>
                                <td>
                                    <?php 
                                        $estado = $fila['estado'];
                                        $clase_badge = "badge-pendiente";
                                        if (strtolower($estado) === 'aprobado') $clase_badge = "badge-aprobado";
                                        if (strtolower($estado) === 'rechazado') $clase_badge = "badge-rechazado";
                                    ?>
                                    <span class="badge <?php echo $clase_badge; ?>"><?php echo htmlspecialchars($estado); ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="sin-datos">
                    <p>Aún no cuentas con postulaciones registradas en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>