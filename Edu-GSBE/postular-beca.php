<?php
session_start();

// Conexión a la base de datos
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion   = new mysqli($servidor, $usuario, $password, $base_datos);

// Obtener ID del estudiante logueado (por defecto 1 si no hay sesión activa para pruebas)
$usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 1;

// Valores por defecto
$nombres_estudiante   = "Estudiante";
$apellidos_estudiante = "";
$documento_estudiante = "No registrado";
$correo_estudiante    = "No registrado";
$promedio_registrado  = "4.50"; 

// Cargar datos del usuario desde la base de datos de forma segura
if (!$conexion->connect_error) {
    $stmt_user = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
    if ($stmt_user) {
        $stmt_user->bind_param("i", $usuario_id);
        $stmt_user->execute();
        $res = $stmt_user->get_result();
        if ($datos = $res->fetch_assoc()) {
            $nombres_estudiante   = $datos['nombres'] ?? ($datos['nombre'] ?? 'Estudiante');
            $apellidos_estudiante = $datos['apellidos'] ?? ($datos['apellido'] ?? '');
            $documento_estudiante = $datos['numero_documento'] ?? ($datos['documento'] ?? 'No registrado');
            $correo_estudiante    = $datos['correo'] ?? ($datos['email'] ?? 'No registrado');
            if (isset($datos['promedio']) && !empty($datos['promedio'])) {
                $promedio_registrado = $datos['promedio'];
            }
        }
        $stmt_user->close();
    }
}

// Obtener la beca seleccionada según el parámetro 'id' en la URL
$beca_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$mensaje = "";

$nombre_beca = "Beca Excelencia Académica";
if($beca_id == 2) $nombre_beca = "Beca Apoyo Vulnerabilidad";
if($beca_id == 3) $nombre_beca = "Beca Talento Deportivo";
if($beca_id == 4) $nombre_beca = "Beca Semillero de Investigación";
if($beca_id == 5) $nombre_beca = "Beca Intercambio Académico";
if($beca_id == 6) $nombre_beca = "Beca Liderazgo y Representación";

// Procesar envío de postulación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_postulacion'])) {
    $estado   = 'Pendiente';
    $promedio = $_POST['promedio'] ?? $promedio_registrado;

    if (!$conexion->connect_error) {
        $stmt = $conexion->prepare("INSERT INTO solicitudes (usuario_id, tipo_beca, estado, fecha_solicitud) VALUES (?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("iss", $usuario_id, $nombre_beca, $estado);
            if ($stmt->execute()) {
                $mensaje = "¡Postulación enviada con éxito! Su promedio de " . htmlspecialchars($promedio) . " fue registrado correctamente.";
            } else {
                $mensaje = "Error al registrar la postulación: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulación a Convocatoria - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; padding: 12px 20px; }
        .logo { height: 40px; }
        .pagina { max-width: 850px; margin: 30px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        .volver { display: inline-block; margin-bottom: 15px; color: #2a5fb0; text-decoration: none; font-size: 13px; font-weight: 600; }
        .volver:hover { text-decoration: underline; }
        h1 { color: #14325c; font-size: 24px; margin-bottom: 6px; font-weight: 700; }
        .intro { color: #64748b; font-size: 13px; margin-bottom: 20px; }

        .tarjeta-form { background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .seccion-titulo { font-size: 15px; color: #14325c; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 18px; font-weight: 700; }
        
        .grid-datos { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .campo { display: flex; flex-direction: column; }
        .campo.lleno { grid-column: span 2; }
        .campo label { font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px; }
        .campo input { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #f8fafc; color: #1e293b; font-weight: 500; }
        .campo input.editable { background: #fff; border-color: #005691; font-weight: bold; color: #005691; }

        .btn-enviar { background-color: #005691; color: #fff; border: none; padding: 13px; border-radius: 6px; font-weight: bold; width: 100%; cursor: pointer; font-size: 14px; transition: background 0.2s; margin-top: 10px; }
        .btn-enviar:hover { background-color: #14325c; }
        .alerta-exito { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; font-weight: bold; }
        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 20px; text-align: center; font-size: 12px; margin-top: 40px; }
    </style>
</head>
<body>
    <header>
        <a href="panel-estudiante.php"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
    </header>

    <main class="pagina">
        <a href="lista-becas.php" class="volver">&#8249; Volver a Convocatorias de Becas</a>
        
        <h1>Postulación a Convocatoria</h1>
        <p class="intro">Verifica tus datos institucionales, ingresa tu promedio actual y confirma tu solicitud.</p>

        <div class="tarjeta-form">
            <?php if (!empty($mensaje)): ?>
                <div class="alerta-exito"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="seccion-titulo">1. Datos del Postulante</div>
                
                <div class="grid-datos">
                    <div class="campo">
                        <label>Nombres y Apellidos</label>
                        <input type="text" value="<?php echo htmlspecialchars($nombres_estudiante . ' ' . $apellidos_estudiante); ?>" disabled>
                    </div>

                    <div class="campo">
                        <label>Número de Cédula / Documento</label>
                        <input type="text" value="<?php echo htmlspecialchars($documento_estudiante); ?>" disabled>
                    </div>

                    <div class="campo">
                        <label>Correo Electrónico Institucional</label>
                        <input type="text" value="<?php echo htmlspecialchars($correo_estudiante); ?>" disabled>
                    </div>

                    <div class="campo">
                        <label>Promedio Académico (Indica tu promedio de 1.0 a 5.0) *</label>
                        <input type="number" step="0.01" min="1.0" max="5.0" name="promedio" class="editable" value="<?php echo htmlspecialchars($promedio_registrado); ?>" required placeholder="Ej: 3.91 o 4.5">
                    </div>
                </div>

                <div class="seccion-titulo">2. Detalles de la Beca</div>

                <div class="grid-datos">
                    <div class="campo lleno">
                        <label>Convocatoria Seleccionada</label>
                        <input type="text" value="<?php echo htmlspecialchars($nombre_beca); ?>" disabled style="background: #eef2ff; color: #1e40af; font-weight: bold;">
                    </div>

                    <div class="campo lleno">
                        <label>Estado Inicial de la Solicitud</label>
                        <input type="text" value="Pendiente de Revisión por Administración" disabled>
                    </div>
                </div>

                <button type="submit" name="enviar_postulacion" class="btn-enviar">Confirmar y Enviar Solicitud</button>
            </form>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>