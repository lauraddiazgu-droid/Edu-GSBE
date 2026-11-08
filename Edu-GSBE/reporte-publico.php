<?php
session_start();

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion   = new mysqli($servidor, $usuario, $password, $base_datos);

$mensaje_envio = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificar_admin'])) {
    $correo_solicitante = !empty($_POST['correo_contacto']) ? trim($_POST['correo_contacto']) : 'Anónimo';
    $asunto_reporte    = trim($_POST['asunto_reporte']);
    $detalles          = trim($_POST['detalles']);
    
    if (!$conexion->connect_error) {
        $stmt = $conexion->prepare("INSERT INTO notificaciones (correo_usuario, asunto, mensaje, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $correo_solicitante, $asunto_reporte, $detalles);
        if ($stmt->execute()) {
            $mensaje_envio = "¡Su reporte ha sido enviado exitosamente al equipo administrativo de Edu-GSBE!";
        } else {
            $mensaje_envio = "Error al registrar el reporte en la base de datos.";
        }
        $stmt->close();
    } else {
        $mensaje_envio = "Error de conexión con la base de datos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Reportes y PQRS - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; padding: 12px 20px; }
        .logo { height: 40px; }
        .pagina { max-width: 800px; margin: 40px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        .volver { display: inline-block; margin-bottom: 15px; color: #2a5fb0; text-decoration: none; font-size: 13px; font-weight: 500; }
        .volver:hover { text-decoration: underline; }
        h1 { color: #14325c; font-size: 24px; margin-bottom: 8px; font-weight: 700; }
        .intro { color: #64748b; font-size: 13px; margin-bottom: 25px; line-height: 1.5; }

        .tarjeta-form { background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .tarjeta-form h2 { font-size: 18px; color: #14325c; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 8px; }

        .campo { display: flex; flex-direction: column; margin-bottom: 16px; }
        .campo label { font-size: 12px; font-weight: bold; color: #334155; margin-bottom: 6px; }
        .campo input, .campo textarea { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; }
        .campo input:focus, .campo textarea:focus { border-color: #005691; box-shadow: 0 0 0 3px rgba(0, 86, 145, 0.1); }
        
        .btn-enviar { background-color: #005691; color: #fff; border: none; padding: 12px; border-radius: 6px; font-weight: bold; width: 100%; cursor: pointer; font-size: 14px; transition: background 0.2s; }
        .btn-enviar:hover { background-color: #14325c; }
        .alerta-exito { background: #dcfce7; color: #16a34a; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; font-weight: bold; }

        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 20px; text-align: center; font-size: 12px; margin-top: 40px; }
    </style>
</head>
<body>
    <header>
        <a href="index.html"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
    </header>

    <main class="pagina">
        <a href="index.html" class="volver">&#8249; Volver al Inicio</a>
        <h1>Mesa de Ayuda y Envío de Reportes</h1>
        <p class="intro">¿Tienes alguna duda, anomalía en un pago o sugerencia? Envía tu reporte directamente a nuestro equipo administrativo sin necesidad de registro previo.</p>

        <div class="tarjeta-form">
            <h2>Formulario de Reporte Ciudadano / Estudiantil</h2>
            <?php if (!empty($mensaje_envio)) { echo "<div class='alerta-exito'>$mensaje_envio</div>"; } ?>

            <form action="" method="POST">
                <div class="campo">
                    <label>Correo de Contacto (Opcional)</label>
                    <input type="email" name="correo_contacto" placeholder="tucorreo@ejemplo.com">
                </div>

                <div class="campo">
                    <label>Asunto del Reporte <span style="color: red;">*</span></label>
                    <input type="text" name="asunto_reporte" placeholder="Ej. Error al adjuntar comprobante o duda sobre convocatoria" required>
                </div>

                <div class="campo">
                    <label>Descripción detallada <span style="color: red;">*</span></label>
                    <textarea name="detalles" rows="5" placeholder="Explica detalladamente tu caso..." required></textarea>
                </div>

                <button type="submit" name="notificar_admin" class="btn-enviar">Enviar Reporte al Administrador</button>
            </form>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>