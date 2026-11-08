<?php
session_start();

// Proteger el acceso: solo administradores pueden ver la bandeja de reportes
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    echo "<script>
            alert('Acceso denegado. Debe iniciar sesión como Administrador.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion   = new mysqli($servidor, $usuario, $password, $base_datos);

$reportes_recogidos = [];
if (!$conexion->connect_error) {
    $res_rep = $conexion->query("SELECT * FROM notificaciones ORDER BY id DESC");
    if ($res_rep) {
        while($fila = $res_rep->fetch_assoc()) {
            $reportes_recogidos[] = $fila;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Reportes y PQRS - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; padding: 12px 20px; }
        .logo { height: 40px; }
        .pagina { max-width: 1000px; margin: 30px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        .volver { display: inline-block; margin-bottom: 15px; color: #2a5fb0; text-decoration: none; font-size: 13px; font-weight: 500; }
        .volver:hover { text-decoration: underline; }
        h1 { color: #14325c; font-size: 24px; margin-bottom: 8px; font-weight: 700; }
        .intro { color: #64748b; font-size: 13px; margin-bottom: 25px; }

        .seccion-card { background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        th, td { padding: 12px; border-bottom: 1px solid #edf2f7; }
        th { background-color: #f8fafc; color: #14325c; font-weight: bold; }
        td { color: #334155; }
        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 20px; text-align: center; font-size: 12px; margin-top: 40px; }
    </style>
</head>
<body>
    <header>
        <a href="panel-admin.php"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
    </header>

    <main class="pagina">
        <a href="panel-admin.php" class="volver">&#8249; Volver al Panel de Administración</a>
        <h1>Bandeja de Reportes, Quejas y Sugerencias (PQRS)</h1>
        <p class="intro">Listado completo de solicitudes e inquietudes enviadas por los usuarios de la plataforma.</p>

        <div class="seccion-card">
            <?php if (count($reportes_recogidos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th>Mensaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportes_recogidos as $rep): ?>
                        <tr>
                            <td style="color: #64748b; white-space: nowrap;"><?php echo htmlspecialchars($rep['fecha']); ?></td>
                            <td style="font-weight: bold; color: #334155;"><?php echo htmlspecialchars($rep['correo_usuario']); ?></td>
                            <td style="color: #005691; font-weight: bold;"><?php echo htmlspecialchars($rep['asunto']); ?></td>
                            <td><?php echo htmlspecialchars($rep['mensaje']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #64748b; font-size: 13px;">No hay reportes ni PQRS registrados por los usuarios en el sistema actualmente.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>