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

// 1. Obtener el nombre del estudiante logueado
$sql_user = "SELECT nombre FROM usuarios WHERE id = '$usuario_id' LIMIT 1";
$res_user = $conexion->query($sql_user);
$nombre_estudiante = ($res_user && $res_user->num_rows > 0) ? $res_user->fetch_assoc()['nombre'] : 'Estudiante Edu-GSBE';

// 2. Consultar TODAS las solicitudes de crédito APROBADAS del estudiante
$sql_creditos = "SELECT * FROM solicitudes 
                 WHERE usuario_id = '$usuario_id' 
                   AND (estado = 'aprobada' OR estado = 'aprobado')
                   AND (motivo LIKE '%Crédito%' OR motivo LIKE '%Préstamo%') 
                 ORDER BY id DESC";
$res_creditos = $conexion->query($sql_creditos);

$lista_creditos = [];
if ($res_creditos && $res_creditos->num_rows > 0) {
    while ($c = $res_creditos->fetch_assoc()) {
        $lista_creditos[] = $c;
    }
}

// 3. Determinar cuál crédito está seleccionado actualmente
$solicitud_id_sel = isset($_GET['credito_id']) ? intval($_GET['credito_id']) : ($lista_creditos[0]['id'] ?? 0);

$datos_credito_sel = null;
foreach ($lista_creditos as $cred) {
    if ($cred['id'] == $solicitud_id_sel) {
        $datos_credito_sel = $cred;
        break;
    }
}

if (!$datos_credito_sel && count($lista_creditos) > 0) {
    $datos_credito_sel = $lista_creditos[0];
}

// 4. Extraer datos reales del motivo guardado (Monto, Plazo, Concepto)
$nombre_prestamo = "Crédito Educativo Financiero";
$monto_total = 1890000;
$total_cuotas = 6;
$fecha_solicitud = date('Y-m-d');

if ($datos_credito_sel) {
    $fecha_solicitud = $datos_credito_sel['fecha_solicitud'];
    $motivo = $datos_credito_sel['motivo'];

    if (preg_match('/Solicitud de Crédito Financiero:\s*([^\|]+)/i', $motivo, $m_nom)) {
        $nombre_prestamo = trim($m_nom[1]);
    }
    if (preg_match('/Monto:\s*\$?([\d\.]+)/i', $motivo, $m_monto)) {
        $monto_total = floatval(str_replace('.', '', $m_monto[1]));
    }
    if (preg_match('/Plazo:\s*(\d+)/i', $motivo, $m_plazo)) {
        $total_cuotas = intval($m_plazo[1]);
    }
}

if ($total_cuotas <= 0) $total_cuotas = 1;
$monto_cuota = round($monto_total / $total_cuotas);

// 5. Lógica de fechas dinámicas
$fecha_inicio_obj = new DateTime(!empty($fecha_solicitud) ? $fecha_solicitud : 'now');
$fecha_inicio_str = $fecha_inicio_obj->format('d/m/Y');

// 6. Obtener el código exacto de este crédito (ej. SOL-000012)
$cod_credito = $datos_credito_sel ? (!empty($datos_credito_sel['codigo']) ? $datos_credito_sel['codigo'] : ('SOL-' . str_pad($datos_credito_sel['id'], 6, '0', STR_PAD_LEFT))) : 'GENERAL';

// 7. Contar EXCLUSIVAMENTE los pagos asociados a ESTE código de crédito específico
$sql_pagos_count = "SELECT COUNT(*) as total FROM pagos_estudiantes WHERE estudiante = '$nombre_estudiante' AND referencia LIKE '%[$cod_credito]%'";
$res_pagos_count = $conexion->query($sql_pagos_count);
$cuotas_pagadas_count = ($res_pagos_count) ? intval($res_pagos_count->fetch_assoc()['total']) : 0;

$proxima_cuota_obligatoria = $cuotas_pagadas_count + 1;
if ($proxima_cuota_obligatoria > $total_cuotas) {
    $proxima_cuota_obligatoria = $total_cuotas;
}

// 8. Generar las fechas del plan dinámico según los pagos de ESTE crédito
$plan_cuotas = [];
$fecha_vencimiento_final = "";
$total_dinero_pagado = 0;

for ($i = 1; $i <= $total_cuotas; $i++) {
    $fecha_cuota = clone $fecha_inicio_obj;
    $fecha_cuota->modify("+$i month");
    
    if ($i <= $cuotas_pagadas_count) {
        $estado_cuota = 'Pagada';
        $total_dinero_pagado += $monto_cuota;
    } else {
        $estado_cuota = 'Pendiente';
    }
    
    $plan_cuotas[] = [
        'numero' => $i,
        'fecha_limite' => $fecha_cuota->format('d/m/Y'),
        'valor' => $monto_cuota,
        'estado' => $estado_cuota
    ];

    if ($i === $total_cuotas) {
        $fecha_vencimiento_final = $fecha_cuota->format('d/m/Y');
    }
}

$saldo_pendiente = $monto_total - $total_dinero_pagado;
if ($saldo_pendiente < 0) $saldo_pendiente = 0;

// 9. Procesar el pago con comprobante y referencia única vinculada al crédito
$mensaje_exito = "";
$clase_alerta = "alerta-exito";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pago'])) {
    $monto_pagado = floatval($_POST['monto_pago']);
    $cuota_pagada = intval($_POST['numero_cuota']);
    $banco_destino = $conexion->real_escape_string($_POST['banco_destino']);
    $ref_ingresada = $conexion->real_escape_string($_POST['referencia']);
    
    // Vincular la referencia con el código del crédito seleccionado
    $referencia_final = $ref_ingresada . " [" . $cod_credito . "]";

    $verificar_ref = $conexion->query("SELECT id FROM pagos_estudiantes WHERE referencia = '$referencia_final'");
    
    if ($verificar_ref && $verificar_ref->num_rows > 0) {
        $mensaje_exito = "Error: La referencia '$ref_ingresada' ya fue registrada para este crédito. Ingrese una diferente.";
        $clase_alerta = "alerta-error";
    } else {
        if (isset($_FILES['comprobante_foto']) && $_FILES['comprobante_foto']['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = time() . '_' . basename($_FILES['comprobante_foto']['name']);
            $directorio = 'uploads/';
            
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            
            $ruta_destino = $directorio . $nombre_archivo;
            
            if (move_uploaded_file($_FILES['comprobante_foto']['tmp_name'], $ruta_destino)) {
                $stmt = $conexion->prepare("INSERT INTO pagos_estudiantes (estudiante, valor, banco, referencia, foto, estado) VALUES (?, ?, ?, ?, ?, 'Pendiente')");
                $stmt->bind_param("sdsss", $nombre_estudiante, $monto_pagado, $banco_destino, $referencia_final, $ruta_destino);
                $stmt->execute();

                echo "<script>alert('¡Pago de la Cuota $cuota_pagada reportado con éxito para $cod_credito!'); window.location.href='detalle-prestamo.php?credito_id=" . ($datos_credito_sel['id'] ?? '') . "';</script>";
                exit();
            } else {
                $mensaje_exito = "Error al guardar la foto del comprobante.";
                $clase_alerta = "alerta-error";
            }
        } else {
            $mensaje_exito = "Error: Es obligatorio adjuntar la foto del comprobante de pago.";
            $clase_alerta = "alerta-error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle y Pagos de mi Préstamo - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; }
        .mi-contenedor { display: flex; align-items: center; padding: 10px 20px; background-color: #d6d4d4; }
        .logo { height: 45px; width: auto; }
        .pagina { max-width: 1000px; margin: 30px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        .volver { display: inline-block; margin-bottom: 15px; color: #2a5fb0; text-decoration: none; font-size: 13px; font-weight: 500; }
        .volver:hover { text-decoration: underline; }
        h1 { color: #14325c; font-size: 26px; margin-bottom: 8px; font-weight: 700; }
        .intro { color: #555; font-size: 14px; margin-bottom: 20px; }

        .selector-credito { background: #fff; border: 2px solid #005691; border-radius: 10px; padding: 15px 20px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; gap: 15px; }
        .selector-credito label { font-size: 13px; font-weight: bold; color: #14325c; }
        .selector-credito select { padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; font-weight: bold; color: #005691; background: #f8fafc; cursor: pointer; flex-grow: 1; max-width: 500px; }

        .tarjetas-resumen { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .tarjeta-fin { background: #fff; border-radius: 10px; padding: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border-left: 5px solid #e07a5f; }
        .tarjeta-fin h3 { font-size: 12px; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }
        .tarjeta-fin .valor { font-size: 18px; font-weight: bold; color: #14325c; }
        .tarjeta-fin .detalle-sub { font-size: 11px; color: #64748b; margin-top: 4px; }

        .grid-contenido { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 20px; }
        @media (max-width: 850px) { .grid-contenido { grid-template-columns: 1fr; } }

        .seccion-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); margin-bottom: 20px; }
        .seccion-card h2 { font-size: 16px; color: #14325c; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 8px; }

        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #edf2f7; }
        th { background-color: #f8fafc; color: #14325c; font-weight: bold; }
        td { color: #334155; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .pendiente { background: #fef3c7; color: #d97706; }
        .pagada { background: #dcfce7; color: #16a34a; }

        .campo { display: flex; flex-direction: column; margin-bottom: 12px; }
        .campo label { font-size: 11px; font-weight: bold; color: #334155; margin-bottom: 4px; }
        .campo input, .campo select { padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; outline: none; }
        .input-bloqueado { background-color: #e2e8f0; color: #475569; font-weight: bold; cursor: not-allowed; }
        .btn-pagar { background-color: #005691; color: #fff; border: none; padding: 10px; border-radius: 6px; font-weight: bold; width: 100%; cursor: pointer; font-size: 13px; }
        .btn-pagar:hover { background-color: #14325c; }

        .seccion-banco { display: none; background: #f8fafc; border: 1px solid #cbd5e1; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 12px; }
        .seccion-banco img { width: 140px; height: 140px; margin: 8px auto; display: block; border-radius: 6px; border: 1px solid #94a3b8; object-fit: contain; background: #fff; }

        .alerta-exito { background: #dcfce7; color: #16a34a; padding: 10px; border-radius: 6px; font-size: 12px; margin-bottom: 15px; font-weight: bold; }
        .alerta-error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; font-size: 12px; margin-bottom: 15px; font-weight: bold; }
        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 25px 20px; margin-top: 40px; text-align: center; font-size: 12px; }
    </style>
    <script>
        function cambiarBancoPago() {
            let banco = document.getElementById('banco_destino').value;
            let divNequi = document.getElementById('qr-nequi');
            let divDavivienda = document.getElementById('qr-davivienda');

            divNequi.style.display = 'none';
            divDavivienda.style.display = 'none';

            if (banco === 'Nequi / Daviplata') {
                divNequi.style.display = 'block';
            } else if (banco === 'Davivienda') {
                divDavivienda.style.display = 'block';
            }
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
        <a href="panel-estudiante.php" class="volver">&#8249; Volver al Panel Estudiantil</a>
        <h1>Detalle Financiero y Estado de Crédito</h1>
        <p class="intro">Selecciona cuál de tus créditos aprobados deseas consultar o pagar.</p>
        
        <!-- SELECTOR DE CRÉDITO APROBADO -->
        <?php if (count($lista_creditos) > 0): ?>
            <div class="selector-credito">
                <label for="credito_id">Seleccionar Crédito Aprobado:</label>
                <select name="credito_id" id="credito_id" onchange="location.href='detalle-prestamo.php?credito_id=' + this.value;">
                    <?php foreach ($lista_creditos as $c_item): ?>
                        <?php 
                            $codigo_c = !empty($c_item['codigo']) ? $c_item['codigo'] : ('SOL-' . str_pad($c_item['id'], 6, '0', STR_PAD_LEFT));
                            $selected = ($c_item['id'] == $solicitud_id_sel) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $c_item['id']; ?>" <?php echo $selected; ?>>
                            <?php echo $codigo_c; ?> - <?php echo htmlspecialchars($c_item['motivo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <!-- RESUMEN DE SALDOS DEL CRÉDITO SELECCIONADO -->
        <div class="tarjetas-resumen">
            <div class="tarjeta-fin">
                <h3>Crédito Aprobado</h3>
                <div class="valor" style="font-size: 14px; margin-top: 4px;"><?php echo htmlspecialchars($nombre_prestamo); ?></div>
                <div class="detalle-sub">Monto total: $<?php echo number_format($monto_total, 0, ',', '.'); ?> COP (<?php echo $cod_credito; ?>)</div>
            </div>
            <div class="tarjeta-fin" style="border-left-color: #005691;">
                <h3>Vigencia del Crédito</h3>
                <div class="valor" style="font-size: 14px; margin-top: 4px;">Inicia: <?php echo $fecha_inicio_str; ?></div>
                <div class="detalle-sub">Vence: <?php echo $fecha_vencimiento_final; ?></div>
            </div>
            <div class="tarjeta-fin" style="border-left-color: #10b981;">
                <h3>Total Pagado</h3>
                <div class="valor">$<?php echo number_format($total_dinero_pagado, 0, ',', '.'); ?> COP</div>
                <div class="detalle-sub"><?php echo $cuotas_pagadas_count; ?> de <?php echo $total_cuotas; ?> cuotas canceladas</div>
            </div>
            <div class="tarjeta-fin" style="border-left-color: #f59e0b;">
                <h3>Saldo Pendiente Actual</h3>
                <div class="valor">$<?php echo number_format($saldo_pendiente, 0, ',', '.'); ?> COP</div>
                <div class="detalle-sub">Siguiente pago: Cuota <?php echo $proxima_cuota_obligatoria; ?> de <?php echo $total_cuotas; ?></div>
            </div>
        </div>

        <div class="grid-contenido">
            <!-- TABLA DE CUOTAS DINÁMICAS -->
            <div>
                <div class="seccion-card">
                    <h2>Plan de Amortización y Cuotas (<?php echo $total_cuotas; ?> Meses)</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Mes / Cuota</th>
                                <th>Fecha Límite</th>
                                <th>Valor Cuota</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plan_cuotas as $cuota): ?>
                            <tr>
                                <td>Cuota <?php echo $cuota['numero']; ?> de <?php echo $total_cuotas; ?></td>
                                <td><?php echo $cuota['fecha_limite']; ?></td>
                                <td>$<?php echo number_format($cuota['valor'], 0, ',', '.'); ?> COP</td>
                                <td>
                                    <?php if ($cuota['estado'] === 'Pagada'): ?>
                                        <span class="badge pagada">Cuota Pagada</span>
                                    <?php else: ?>
                                        <span class="badge pendiente">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FORMULARIO DE REPORTE DE PAGO -->
            <div>
                <div class="seccion-card">
                    <h2>Reportar Pago de Cuota</h2>
                    <?php if (!empty($mensaje_exito)) { echo "<div class='$clase_alerta'>$mensaje_exito</div>"; } ?>
                    
                    <?php if ($cuotas_pagadas_count >= $total_cuotas): ?>
                        <div style="background: #dcfce7; color: #16a34a; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold;">
                            ¡Felicitaciones! Has pagado la totalidad de las cuotas de este crédito (<?php echo $cod_credito; ?>).
                        </div>
                    <?php else: ?>
                        <form action="detalle-prestamo.php?credito_id=<?php echo $solicitud_id_sel; ?>" method="POST" enctype="multipart/form-data">
                            <div class="campo">
                                <label>Cuota Obligatoria a Pagar en Orden</label>
                                <input type="hidden" name="numero_cuota" value="<?php echo $proxima_cuota_obligatoria; ?>">
                                <input type="text" value="Cuota <?php echo $proxima_cuota_obligatoria; ?> de <?php echo $total_cuotas; ?>" readonly class="input-bloqueado" style="background-color: #fef3c7; color: #b45309;">
                            </div>

                            <div class="campo">
                                <label>Valor a Pagar Obligatorio (COP)</label>
                                <input type="number" name="monto_pago" value="<?php echo $monto_cuota; ?>" readonly class="input-bloqueado">
                            </div>
                            
                            <div class="campo">
                                <label>Banco Destino / Pasarela</label>
                                <select name="banco_destino" id="banco_destino" required onchange="cambiarBancoPago()">
                                    <option value="">Seleccione el banco o pasarela</option>
                                    <option value="Nequi / Daviplata">Nequi / Daviplata</option>
                                    <option value="Davivienda">Davivienda</option>
                                </select>
                            </div>

                            <div id="qr-nequi" class="seccion-banco">
                                <p style="font-weight: bold; color: #581c87;">Escanea el código QR de Nequi Negocios:</p>
                                <img src="img/qr-nequi.jpg" alt="QR Nequi Laura Diaz">
                                <p style="margin-top: 4px;">Llave: <strong>0093521712</strong> (Laura Diaz)</p>
                            </div>

                            <div id="qr-davivienda" class="seccion-banco">
                                <p style="font-weight: bold; color: #991b1b;">Escanea el código QR de Davivienda:</p>
                                <img src="img/qr-davivienda.jpg" alt="QR Davivienda Edu-GSBE" onerror="this.src='qr-nequi.jpg';">
                                <p style="margin-top: 4px;">Cuenta de Ahorros: <strong>4589-0012-8834</strong></p>
                            </div>

                            <div class="campo">
                                <label>Número de Comprobante / Referencia</label>
                                <input type="text" name="referencia" placeholder="Ej. NQ-98765432" required>
                            </div>

                            <div class="campo">
                                <label>Foto del Comprobante de Pago <span style="color: red;">(Obligatorio)</span></label>
                                <input type="file" name="comprobante_foto" accept="image/*" required style="padding: 6px; background: #fff;">
                            </div>

                            <button type="submit" name="registrar_pago" class="btn-pagar">Enviar Comprobante de Pago</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>