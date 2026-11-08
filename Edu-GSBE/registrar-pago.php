<?php
session_start();
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Consultar los comprobantes reportados por los estudiantes en tiempo real
$resultado_pagos = $conexion->query("SELECT * FROM pagos_estudiantes ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Desembolsos y Registro de Pagos - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #000; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; }
        .mi-contenedor { display: flex; align-items: center; padding: 10px 20px; background-color: #d6d4d4; }
        .logo { height: 45px; width: auto; }
        .pagina { max-width: 1200px; margin: 30px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        
        .volver { display: inline-block; margin-bottom: 12px; color: #1d4ed8; text-decoration: none; font-size: 14px; font-weight: 600; }
        .volver:hover { text-decoration: underline; }

        h1, h2 { color: #000; font-weight: 700; }
        h1 { font-size: 26px; margin-bottom: 20px; }
        h2 { font-size: 20px; margin-bottom: 12px; margin-top: 35px; }

        .tabla-container { background: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        th, td { padding: 14px 18px; border-bottom: 1px solid #cbd5e1; color: #000; vertical-align: middle; }
        th { background-color: #0f766e; color: #fff; font-weight: bold; }
        
        .btn-comprobante { background-color: #0284c7; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 12px; display: inline-block; }
        .btn-comprobante:hover { background-color: #0369a1; }

        .sin-resultados { padding: 25px; text-align: center; color: #000; font-size: 13px; font-style: italic; }
        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 25px 20px; margin-top: 40px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <header>
        <div class="mi-contenedor">
            <a href="panel-admin.php"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
        </div>
    </header>
    
    <main class="pagina">
        <a href="panel-admin.php" class="volver">&#8249; Volver al Panel Administrador</a>
        <h1>Control de Desembolsos y Registro de Pagos (Comprobantes de Estudiantes)</h1>

        <!-- TABLA DE PAGOS REPORTADOS POR ESTUDIANTES -->
        <div class="tabla-container">
            <?php if ($resultado_pagos && $resultado_pagos->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Estudiante</th>
                            <th>Valor Cuota</th>
                            <th>Banco / Pasarela</th>
                            <th>Referencia</th>
                            <th>Comprobante (Foto)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pago = $resultado_pagos->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $pago['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($pago['estudiante']); ?></td>
                                <td><strong>$<?php echo number_format($pago['valor'], 0, ',', '.'); ?> COP</strong></td>
                                <td><?php echo htmlspecialchars($pago['banco']); ?></td>
                                <td><?php echo htmlspecialchars($pago['referencia']); ?></td>
                                <td>
                                    <?php if (!empty($pago['foto'])): ?>
                                        <a href="<?php echo htmlspecialchars($pago['foto']); ?>" target="_blank" class="btn-comprobante">Ver Comprobante</a>
                                    <?php else: ?>
                                        <span style="color: #64748b;">Sin archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><span style="color: #16a34a; font-weight: bold;"><?php echo htmlspecialchars($pago['estado'] ?? 'Pendiente de Revisión'); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="sin-resultados">No hay pagos o comprobantes reportados por estudiantes en este momento.</div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>
</body>
</html>