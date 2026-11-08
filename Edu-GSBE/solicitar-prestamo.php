<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Debe iniciar sesión para continuar.'); window.location.href = 'login.html';</script>";
    exit();
}

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del préstamo desde la URL
$nombre_credito = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : 'Crédito Educativo Financiero';
$tasa_valor = isset($_GET['tasa']) ? floatval($_GET['tasa']) : 1.1;
$tipo_tasa = isset($_GET['tipo_tasa']) ? htmlspecialchars($_GET['tipo_tasa']) : 'M.V.';

// Obtener datos del estudiante registrado
$sql_usuario = "SELECT u.documento, u.nombre, u.apellido, u.email, COALESCE(p.telefono, '') AS telefono 
                FROM usuarios u LEFT JOIN perfil_estudiante p ON u.id = p.usuario_id WHERE u.id = '$usuario_id'";
$res_usuario = $conexion->query($sql_usuario);
$datos = ($res_usuario && $res_usuario->num_rows > 0) ? $res_usuario->fetch_assoc() : ['documento'=>'', 'nombre'=>'', 'apellido'=>'', 'email'=>'', 'telefono'=>''];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_prestamo'])) {
    $monto = floatval($_POST['monto']);
    $cuotas = intval($_POST['cuotas']);
    $entidad_bancaria = $conexion->real_escape_string($_POST['entidad_bancaria']);
    $tipo_cuenta = $conexion->real_escape_string($_POST['tipo_cuenta']);
    $numero_cuenta = $conexion->real_escape_string($_POST['numero_cuenta']);
    $motivo = $conexion->real_escape_string($_POST['motivo']);

    $resumen_financiero = "Solicitud de Crédito Financiero: $nombre_credito | Monto: $$monto COP | Plazo: $cuotas meses | Tasa: $tasa_valor% $tipo_tasa | Banco: $entidad_bancaria ($tipo_cuenta N° $numero_cuenta) | Propósito: $motivo";
    $estado = 'pendiente';

    // Consulta SQL corregida utilizando únicamente las columnas existentes de la tabla solicitudes
    $sql_insert = "INSERT INTO solicitudes (usuario_id, tipo_beca, estado, fecha_solicitud) 
                   VALUES ('$usuario_id', '$resumen_financiero', '$estado', NOW())";

    if ($conexion->query($sql_insert)) {
        echo "<script>alert('¡Solicitud de Crédito enviada con éxito! La entidad financiera evaluará su plan de pago.'); window.location.href = 'estado-solicitud.php';</script>";
        exit();
    } else {
        $error = "Error al registrar la solicitud de préstamo: " . $conexion->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Crédito Educativo - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; }
        .mi-contenedor { display: flex; align-items: center; padding: 10px 20px; background-color: #d6d4d4; }
        .logo { height: 45px; width: auto; }
        .pagina { max-width: 750px; margin: 30px auto; padding: 0 15px; width: 100%; flex-grow: 1; }
        .volver { display: inline-block; margin-bottom: 12px; color: #2a5fb0; text-decoration: none; font-size: 13px; font-weight: 500; }
        .volver:hover { text-decoration: underline; }
        h1 { color: #14325c; font-size: 24px; margin-bottom: 15px; font-weight: 700; }
        
        .box-info-credito { background-color: #fdf2ef; border-left: 5px solid #e07a5f; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .box-info-credito h2 { font-size: 16px; color: #14325c; margin-bottom: 5px; }
        .box-info-credito p { font-size: 13px; color: #555; }

        .card-form { background: #ffffff; padding: 28px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .seccion-titulo { font-size: 14px; font-weight: bold; color: #e07a5f; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .row-dos { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .campo { display: flex; flex-direction: column; margin-bottom: 16px; }
        .campo label { font-size: 12px; font-weight: bold; color: #2d3748; margin-bottom: 6px; }
        .campo input, .campo select, .campo textarea { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; background-color: #fff; color: #333; }
        .campo input[readonly] { background-color: #f1f5f9; color: #475569; }
        .campo textarea { resize: vertical; height: 80px; }

        .simulador-box { background: #1a2938; color: #fff; padding: 18px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
        .simulador-box h3 { font-size: 14px; color: #e07a5f; margin-bottom: 10px; }
        .simulador-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .simulador-item { background: rgba(255,255,255,0.07); padding: 10px; border-radius: 6px; }
        .simulador-item span { display: block; font-size: 11px; color: #94a3b8; margin-bottom: 2px; }
        .simulador-item strong { font-size: 15px; color: #fff; }

        .btn-enviar { background-color: #e07a5f; color: #ffffff; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s; }
        .btn-enviar:hover { opacity: 0.9; }
        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 25px 20px; margin-top: 40px; text-align: center; font-size: 12px; }
    </style>
    <script>
        function actualizarSimulacion() {
            let monto = parseFloat(document.getElementById('monto').value) || 0;
            let cuotas = parseInt(document.getElementById('cuotas').value) || 1;
            let tasa = parseFloat("<?php echo $tasa_valor; ?>");
            let tipoTasa = "<?php echo $tipo_tasa; ?>";

            let tasaMensual = (tipoTasa === 'M.V.') ? (tasa / 100) : (tasa / 100 / 12);
            let interesAproximadoTotal = monto * tasaMensual * cuotas;
            let totalAPagar = monto + interesAproximadoTotal;
            let cuotaMensual = totalAPagar / cuotas;

            document.getElementById('txtCuota').innerText = "$" + Math.round(cuotaMensual).toLocaleString('es-CO') + " COP";
            document.getElementById('txtTotal').innerText = "$" + Math.round(totalAPagar).toLocaleString('es-CO') + " COP";
            document.getElementById('txtInteres').innerText = "$" + Math.round(interesAproximadoTotal).toLocaleString('es-CO') + " COP";
        }
    </script>
</head>
<body>
    <header>
        <div class="mi-contenedor">
            <a href="panel-estudiante.php"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
        </div>
    </header>

    <main class="pagina">
        <a href="prestamos-disponibles.php" class="volver">&#8249; Volver a préstamos disponibles</a>
        <h1>Solicitud de Crédito Educativo Financiero</h1>

        <div class="box-info-credito">
            <h2><?php echo $nombre_credito; ?></h2>
            <p>Tasa de Interés de la entidad: <strong><?php echo $tasa_valor; ?>% <?php echo $tipo_tasa; ?></strong>. Selecciona el monto y plazo deseado para simular las cuotas.</p>
        </div>

        <div class="card-form">
            <?php if (isset($error)) { echo "<p style='color:red; margin-bottom:15px;'>$error</p>"; } ?>
            <form action="" method="POST">
                
                <div class="seccion-titulo">1. Datos del Titular del Crédito</div>
                <div class="row-dos">
                    <div class="campo">
                        <label>Nombre y Apellidos</label>
                        <input type="text" value="<?php echo htmlspecialchars(trim($datos['nombre'] . ' ' . $datos['apellido'])); ?>" readonly>
                    </div>
                    <div class="campo">
                        <label>Documento de Identidad</label>
                        <input type="text" value="<?php echo htmlspecialchars($datos['documento']); ?>" readonly>
                    </div>
                </div>

                <div class="seccion-titulo">2. Condiciones Financieras y Plazo</div>
                <div class="row-dos">
                    <div class="campo">
                        <label>Monto Solicitado (COP)</label>
                        <input type="number" id="monto" name="monto" placeholder="Ej. 5000000" required oninput="actualizarSimulacion()">
                    </div>
                    <div class="campo">
                        <label>Plazo en Cuotas (Meses)</label>
                        <select id="cuotas" name="cuotas" required onchange="actualizarSimulacion()">
                            <option value="">Selecciona la cantidad de meses</option>
                            <option value="3">3 Meses</option>
                            <option value="6">6 Meses</option>
                            <option value="12">12 Meses</option>
                            <option value="24">24 Meses</option>
                            <option value="36">36 Meses</option>
                            <option value="48">48 Meses</option>
                            <option value="60">60 Meses</option>
                        </select>
                    </div>
                </div>

                <!-- SIMULADOR DINÁMICO DE PAGO DE CUOTAS -->
                <div class="simulador-box">
                    <h3>Proyección y Cálculo Estimado de Cuotas</h3>
                    <div class="simulador-grid">
                        <div class="simulador-item">
                            <span>Valor aprox. cuota mensual:</span>
                            <strong id="txtCuota">$0 COP</strong>
                        </div>
                        <div class="simulador-item">
                            <span>Interés estimado proyectado:</span>
                            <strong id="txtInteres">$0 COP</strong>
                        </div>
                    </div>
                    <div style="margin-top:10px; font-size:12px; border-top:1px solid rgba(255,255,255,0.1); padding-top:8px;">
                        Monto Total Estimado a Cancelar: <strong id="txtTotal" style="color:#e07a5f;">$0 COP</strong>
                    </div>
                </div>

                <div class="seccion-titulo">3. Datos Bancarios para Débito / Cobro de Cuotas</div>
                <div class="row-dos">
                    <div class="campo">
                        <label>Entidad Bancaria</label>
                        <select name="entidad_bancaria" required>
                            <option value="">Seleccione su banco</option>
                            <option value="Davivienda">Davivienda</option>
                            <option value="Nequi / Daviplata">Nequi / Daviplata</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label>Tipo de Cuenta</label>
                        <select name="tipo_cuenta" required>
                            <option value="Cuenta de Ahorros">Cuenta de Ahorros</option>
                            <option value="Cuenta Corriente">Cuenta Corriente</option>
                        </select>
                    </div>
                </div>
                <div class="campo">
                    <label>Número de Cuenta Bancaria / Nequi</label>
                    <input type="text" name="numero_cuenta" placeholder="Ingrese el número de cuenta registrado para el pago" required>
                </div>

                <div class="campo">
                    <label>Destino o Justificación del Préstamo</label>
                    <textarea name="motivo" placeholder="Describa brevemente en qué programa o concepto utilizará el dinero solicitado..." required></textarea>
                </div>

                <button type="submit" name="enviar_prestamo" class="btn-enviar">Radicar Solicitud de Crédito</button>
            </form>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>