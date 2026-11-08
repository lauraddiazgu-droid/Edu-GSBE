<?php
session_start();

// Validar que sea un estudiante con sesión activa
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
            alert('Debe iniciar sesión para enviar un reporte.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion   = new mysqli($servidor, $usuario, $password, $base_datos);

$correo_actual = isset($_SESSION['email']) ? $_SESSION['email'] : 'estudiante@ecci.edu.co';
$mensaje_envio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_reporte_estudiante'])) {
    $asunto_reporte = trim($_POST['asunto_reporte']);
    $detalles       = trim($_POST['detalles']);
    
    if (!$conexion->connect_error) {
        $stmt = $conexion->prepare("INSERT INTO notificaciones (correo_usuario, asunto, mensaje, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $correo_actual, $asunto_reporte, $detalles);
        if ($stmt->execute()) {
            $mensaje_envio = "¡Su reporte ha sido enviado exitosamente al administrador!";
        } else {
            $mensaje_envio = "Error al registrar el reporte.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Reporte - Panel de Estudiante</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; padding: 12px 20px; }
        .logo { height: 40px; }
        .pagina { max-width: 800px; margin: 30px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        .volver { display: inline-block; margin-bottom: 15px; color: #2a5fb0; text-decoration: none; font-size: 13px; font-weight: 500; }
        .volver:hover { text-decoration: underline; }
        h1 { color: #14325c; font-size: 24px; margin-bottom: 8px; font-weight: 700; }
        .intro { color: #64748b; font-size: 13px; margin-bottom: 25px; }

        .tarjeta-form { background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .campo { display: flex; flex-direction: column; margin-bottom: 15px; }
        .campo label { font-size: 12px; font-weight: bold; color: #334155; margin-bottom: 5px; }
        .campo input, .campo textarea { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; }
        .btn-enviar { background-color: #005691; color: #fff; border: none; padding: 11px; border-radius: 6px; font-weight: bold; width: 100%; cursor: pointer; font-size: 13px; }
        .btn-enviar:hover { background-color: #14325c; }
        .alerta-exito { background: #dcfce7; color: #16a34a; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; font-weight: bold; }
        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 20px; text-align: center; font-size: 12px; margin-top: 40px; }
    </style>
</head>
<body>
    <header>
        <a href="panel-estudiante.php"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
    </header>

    <main class="pagina">
        <a href="panel-estudiante.php" class="volver">&#8249; Volver al Panel de Estudiante</a>
        <h1>Mesa de Ayuda Estudiantil</h1>
        <p class="intro">Envía tus peticiones, quejas o reclamos directamente desde tu cuenta institucional.</p>

        <div class="tarjeta-form">
            <?php if (!empty($mensaje_envio)) { echo "<div class='alerta-exito'>$mensaje_envio</div>"; } ?>

            <form action="" method="POST">
                <div class="campo">
                    <label>Remitente (Correo Institucional)</label>
                    <input type="text" value="<?php echo htmlspecialchars($correo_actual); ?>" disabled style="background: #f1f5f9; color: #64748b;">
                </div>

                <div class="campo">
                    <label>Asunto del Reporte <span style="color: red;">*</span></label>
                    <input type="text" name="asunto_reporte" placeholder="Ej. Inconsistencia en cuotas de préstamo" required>
                </div>

                <div class="campo">
                    <label>Mensaje o Detalle <span style="color: red;">*</span></label>
                    <textarea name="detalles" rows="5" placeholder="Escribe tu solicitud aquí..." required></textarea>
                </div>

                <button type="submit" name="enviar_reporte_estudiante" class="btn-enviar">Enviar Solicitud al Administrador</button>
            </form>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>