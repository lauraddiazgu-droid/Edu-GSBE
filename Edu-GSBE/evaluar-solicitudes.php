<?php
session_start();

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

$sql = "SELECT s.id, s.codigo, s.fecha_solicitud, s.estado, s.motivo, 
               u.nombre, u.apellido, b.nombre AS nombre_beca, 
               p.foto, p.promedio_acumulado 
        FROM solicitudes s 
        INNER JOIN usuarios u ON s.usuario_id = u.id 
        LEFT JOIN becas b ON s.beca_id = b.id 
        LEFT JOIN perfil_estudiante p ON u.id = p.usuario_id 
        ORDER BY s.fecha_solicitud DESC";

$resultado = $conexion->query($sql);

// Archivo correcto del panel de administración según tu explorador de archivos
$pagina_inicio_admin = "panel-admin.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluar Solicitudes - Administrador</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #000; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; }
        .mi-contenedor { display: flex; align-items: center; padding: 10px 20px; background-color: #d6d4d4; }
        .logo { height: 45px; width: auto; }
        .pagina { max-width: 1450px; margin: 30px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        
        .volver { display: inline-block; margin-bottom: 12px; color: #1d4ed8; text-decoration: none; font-size: 14px; font-weight: 600; }
        .volver:hover { text-decoration: underline; }

        h1 { color: #1e3a8a; font-size: 26px; margin-bottom: 20px; font-weight: 700; }
        
        .tabla-container { background: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
        th, td { padding: 12px 14px; border-bottom: 1px solid #cbd5e1; vertical-align: middle; color: #000; }
        
        th { background-color: #0f766e; color: #fff; font-weight: bold; }
        
        .badge-estado { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .pendiente { background: #fef3c7; color: #d97706; }
        .aprobada { background: #dcfce7; color: #16a34a; }
        .rechazada { background: #fee2e2; color: #dc2626; }

        .btn-aprobar { background-color: #16a34a; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-rechazar { background-color: #dc2626; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; margin-top: 5px; }
        .btn-aprobar:hover { background-color: #15803d; }
        .btn-rechazar:hover { background-color: #b91c1c; }

        .sin-resultados { padding: 30px; text-align: center; color: #000; font-size: 14px; }
        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 25px 20px; margin-top: 40px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <header>
        <div class="mi-contenedor">
            <a href="<?php echo $pagina_inicio_admin; ?>"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
        </div>
    </header>
    
    <main class="pagina">
        <a href="<?php echo $pagina_inicio_admin; ?>" class="volver">&#8249; Volver al Panel Administrador</a>
        <h1>Gestión y Evaluación de Solicitudes</h1>
        
        <div class="tabla-container">
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Estudiante</th>
                            <th>Tipo</th>
                            <th>Concepto / Beca o Crédito</th>
                            <th>Valor / Monto</th>
                            <th>Plazo / Cuotas</th>
                            <th>Tasa / Banco</th>
                            <th>Promedio</th>
                            <th>Propósito</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <?php 
                                $es_credito = !empty($row['motivo']) && (
                                    stripos($row['motivo'], 'Crédito') !== false || 
                                    stripos($row['motivo'], 'Préstamo') !== false ||
                                    stripos($row['motivo'], 'Solicitud de Crédito') !== false
                                );

                                $nombre_solicitud = "";
                                $monto = "-";
                                $plazo = "-";
                                $tasa = "-";
                                $banco = "-";
                                $proposito = $row['motivo'];

                                if ($es_credito) {
                                    if (preg_match('/Solicitud de Crédito Financiero:\s*([^\|]+)/i', $row['motivo'], $matches)) {
                                        $nombre_solicitud = trim($matches[1]);
                                    } else {
                                        $nombre_solicitud = "Crédito Financiero";
                                    }

                                    if (preg_match('/Monto:\s*([^\|]+)/i', $row['motivo'], $m_matches)) {
                                        $monto = trim($m_matches[1]);
                                    }
                                    if (preg_match('/Plazo:\s*([^\|]+)/i', $row['motivo'], $p_matches)) {
                                        $plazo = trim($p_matches[1]);
                                    }
                                    if (preg_match('/Tasa:\s*([^\|]+)/i', $row['motivo'], $t_matches)) {
                                        $tasa = trim($t_matches[1]);
                                    }
                                    if (preg_match('/Banco de Pago:\s*([^\|]+)/i', $row['motivo'], $b_matches)) {
                                        $banco = trim($b_matches[1]);
                                    }
                                    if (preg_match('/Propósito:\s*(.*)$/i', $row['motivo'], $prop_matches)) {
                                        $proposito = trim($prop_matches[1]);
                                    }
                                } else {
                                    $nombre_solicitud = $row['nombre_beca'] ?? 'Beca General';
                                    $proposito = $row['motivo'] ?? 'Sin observaciones';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo !empty($row['codigo']) ? htmlspecialchars($row['codigo']) : 'SOL-' . str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                <td>
                                    <?php if ($es_credito): ?>
                                        <span style="color: #000; font-weight: bold;">Crédito</span>
                                    <?php else: ?>
                                        <span style="color: #000; font-weight: bold;">Beca</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($nombre_solicitud); ?></td>
                                <td><?php echo htmlspecialchars($monto); ?></td>
                                <td><?php echo htmlspecialchars($plazo); ?></td>
                                <td>
                                    <?php if ($es_credito): ?>
                                        <strong>Tasa:</strong> <?php echo htmlspecialchars($tasa); ?><br>
                                        <strong>Banco:</strong> <?php echo htmlspecialchars($banco); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo $row['promedio_acumulado'] ?? 'N/A'; ?></strong></td>
                                <td><?php echo htmlspecialchars($proposito); ?></td>
                                <td>
                                    <?php 
                                        $estado = strtolower($row['estado']);
                                        $clase_badge = 'pendiente';
                                        if ($estado == 'aprobada' || $estado == 'aprobado') $clase_badge = 'aprobada';
                                        if ($estado == 'rechazada' || $estado == 'rechazado') $clase_badge = 'rechazada';
                                    ?>
                                    <span class="badge-estado <?php echo $clase_badge; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['estado'])); ?></span>
                                </td>
                                <td>
                                    <?php if ($estado == 'pendiente' || $estado == 'en_revision'): ?>
                                        <a href="procesar-evaluacion.php?id=<?php echo $row['id']; ?>&accion=aprobar" class="btn-aprobar">Aprobar</a><br>
                                        <a href="procesar-evaluacion.php?id=<?php echo $row['id']; ?>&accion=rechazar" class="btn-rechazar">Rechazar</a>
                                    <?php else: ?>
                                        <span style="color: #000; font-style: italic; font-size: 11px;">Ya evaluada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="sin-resultados">
                    No hay solicitudes registradas para evaluar en este momento.
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>