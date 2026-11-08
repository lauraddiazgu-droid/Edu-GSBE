<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Debe iniciar sesión para solicitar una beca.'); window.location.href = 'login.html';</script>";
    exit();
}

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

$usuario_id = $_SESSION['usuario_id'];
$beca_id    = isset($_GET['id']) ? intval($_GET['id']) : 1;

// 1. Obtener datos reales de las tablas usuarios y perfil_estudiante
$sql_usuario = "SELECT u.documento, u.nombre, u.apellido, u.email, 
                       COALESCE(p.telefono, '') AS telefono,
                       COALESCE(p.promedio_acumulado, '4.50') AS promedio
                FROM usuarios u
                LEFT JOIN perfil_estudiante p ON u.id = p.usuario_id
                WHERE u.id = '$usuario_id'";
$res_usuario = $conexion->query($sql_usuario);
$datos = ($res_usuario && $res_usuario->num_rows > 0) ? $res_usuario->fetch_assoc() : [
    'documento' => '', 'nombre' => '', 'apellido' => '', 'email' => '', 'telefono' => '', 'promedio' => '4.50'
];

// 2. Obtener información de la beca seleccionada
$sql_beca = "SELECT * FROM becas WHERE id = '$beca_id'";
$res_beca = $conexion->query($sql_beca);
$beca_info = ($res_beca && $res_beca->num_rows > 0) ? $res_beca->fetch_assoc() : [];

$titulo_beca  = $beca_info['nombre_beca'] ?? $beca_info['nombre'] ?? 'Convocatoria Educativa';
$cob_beca     = $beca_info['porcentaje_cobertura'] ?? $beca_info['cobertura'] ?? '100% de la matrícula';
$cierre_beca  = $beca_info['fecha_cierre'] ?? '30/11/2026';

// 3. Guardar solicitud en la base de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_solicitud'])) {
    $motivo_escrito = $conexion->real_escape_string($_POST['motivo']);

    $sql_insert = "INSERT INTO solicitudes (usuario_id, beca_id, fecha_solicitud, estado, motivo) 
                   VALUES ('$usuario_id', '$beca_id', NOW(), 'pendiente', '$motivo_escrito')";

    if ($conexion->query($sql_insert)) {
        echo "<script>
                alert('¡Solicitud registrada con éxito!');
                window.location.href = 'estado-solicitud.php';
              </script>";
        exit();
    } else {
        $error = "Error al registrar: " . $conexion->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar beca | Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        
        header { background: #fff; border-bottom: 1px solid #dcdde1; }
        .mi-contenedor { display: flex; align-items: center; padding: 10px 20px; background-color: #d6d4d4; }
        .logo { height: 45px; width: auto; }

        .pagina { max-width: 650px; margin: 30px auto; padding: 0 15px; width: 100%; flex-grow: 1; }
        .volver { display: inline-block; margin-bottom: 10px; color: #2a5fb0; text-decoration: none; font-size: 13px; font-weight: 500; }
        .volver:hover { text-decoration: underline; }
        
        h1 { color: #14325c; font-size: 24px; margin-bottom: 15px; font-weight: 700; }

        .banner-info { background-color: #fef9e7; border-left: 4px solid #f0b414; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; }
        .banner-info .titulo-beca { font-weight: bold; color: #14325c; font-size: 14px; margin-bottom: 3px; }
        .banner-info .detalles-beca { font-size: 12px; color: #666; }

        .card-form { background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .campo { display: flex; flex-direction: column; margin-bottom: 16px; }
        .campo label { font-size: 12px; font-weight: bold; color: #2d3748; margin-bottom: 6px; }
        .campo input, .campo select, .campo textarea { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; background-color: #fff; color: #333; }
        .campo input[readonly] { background-color: #edf2f7; color: #4a5568; }

        .row-dos { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .campo textarea { resize: vertical; height: 100px; }

        .btn-enviar { background-color: #2b5ca8; color: #ffffff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; align-self: flex-start; margin-top: 5px; }
        .btn-enviar:hover { background-color: #1e447e; }

        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 25px 20px; margin-top: 40px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>

    <header>
        <div class="mi-contenedor">
            <a href="panel-estudiante.php">
                <img src="img/logotipo.png" alt="Edu-GSBE" class="logo">
            </a>
        </div>
    </header>

    <main class="pagina">
        <a href="becas-disponibles.php" class="volver">&#8249; Volver a becas disponibles</a>
        <h1>Solicitar beca</h1>

        <div class="banner-info">
            <div class="titulo-beca"><?php echo htmlspecialchars($titulo_beca); ?></div>
            <div class="detalles-beca">Cobertura: <?php echo htmlspecialchars($cob_beca); ?> · Fecha límite: <?php echo htmlspecialchars($cierre_beca); ?></div>
        </div>

        <div class="card-form">
            <?php if (isset($error)) { echo "<p style='color:red; margin-bottom:15px;'>$error</p>"; } ?>
            <form action="" method="POST">
                
                <div class="campo">
                    <label>Nombre completo</label>
                    <input type="text" value="<?php echo htmlspecialchars(trim($datos['nombre'] . ' ' . $datos['apellido'])); ?>" readonly>
                </div>

                <div class="row-dos">
                    <div class="campo">
                        <label>Documento de identidad</label>
                        <input type="text" value="<?php echo htmlspecialchars($datos['documento']); ?>" readonly>
                    </div>
                    <div class="campo">
                        <label>Teléfono</label>
                        <input type="text" value="<?php echo htmlspecialchars($datos['telefono']); ?>" readonly>
                    </div>
                </div>

                <div class="campo">
                    <label>Correo electrónico</label>
                    <input type="email" value="<?php echo htmlspecialchars($datos['email']); ?>" readonly>
                </div>

                <div class="row-dos">
                    <div class="campo">
                        <label>Programa académico</label>
                        <input type="text" name="programa" placeholder="Ej. Ingeniería de Sistemas" required>
                    </div>
                    <div class="campo">
                        <label>Semestre</label>
                        <select name="semestre" required>
                            <option value="">Selecciona</option>
                            <?php for($i=1; $i<=10; $i++): ?>
                                <option value="<?php echo $i; ?>">Semestre <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="campo">
                    <label>Promedio acumulado (0.0 – 5.0)</label>
                    <input type="text" value="<?php echo htmlspecialchars($datos['promedio']); ?>" readonly>
                </div>

                <div class="campo">
                    <label>¿Por qué mereces esta beca?</label>
                    <textarea name="motivo" required placeholder="Escribe tu justificación..."></textarea>
                </div>

                <button type="submit" name="enviar_solicitud" class="btn-enviar">Enviar solicitud</button>
            </form>
        </div>
    </main>

    <footer class="seccion-contacto">
        <p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p>
    </footer>

</body>
</html>