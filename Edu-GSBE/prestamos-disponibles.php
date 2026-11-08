<?php
session_start();
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Consultar todas las líneas de crédito de la base de datos en tiempo real
$resultado_prestamos = $conexion->query("SELECT * FROM prestamos ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Líneas de Crédito y Préstamos Educativos - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #000; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        header { background: #fff; border-bottom: 1px solid #dcdde1; }
        .mi-contenedor { display: flex; align-items: center; padding: 10px 20px; background-color: #d6d4d4; }
        .logo { height: 45px; width: auto; }
        .pagina { max-width: 1200px; margin: 30px auto; padding: 0 20px; width: 100%; flex-grow: 1; }
        
        .volver { display: inline-block; margin-bottom: 12px; color: #1d4ed8; text-decoration: none; font-size: 14px; font-weight: 600; }
        .volver:hover { text-decoration: underline; }

        h1 { color: #000; font-size: 26px; margin-bottom: 8px; font-weight: 700; }
        p.subtitulo { color: #475569; font-size: 14px; margin-bottom: 30px; }

        .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 25px; }
        .card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s ease; }
        .card:hover { transform: translateY(-4px); }

        .tag-tipo { display: inline-block; background-color: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: bold; margin-bottom: 12px; width: fit-content; }
        .card-titulo { font-size: 18px; font-weight: 700; color: #000; margin-bottom: 10px; }
        .card-desc { font-size: 13px; color: #475569; margin-bottom: 18px; line-height: 1.4; }

        .info-box { background-color: #f8fafc; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #000; }
        .info-box div { margin-bottom: 6px; }
        .info-box div:last-child { margin-bottom: 0; }

        .btn-solicitar { background-color: #e07a5f; color: white; text-align: center; padding: 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; display: block; border: none; cursor: pointer; transition: background 0.2s ease; }
        .btn-solicitar:hover { background-color: #d0694e; }

        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 25px 20px; margin-top: 40px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <header>
        <div class="mi-contenedor">
            <a href="index.html"><img src="img/logotipo.png" alt="Edu-GSBE" class="logo"></a>
        </div>
    </header>

    <main class="pagina">
        <a href="index.html" class="volver">&#8249; Volver a la selección</a>
        <h1>Líneas de Crédito y Préstamos Educativos</h1>
        <p class="subtitulo">Elige la modalidad de financiación que mejor se adapte a tu capacidad de pago y plan de estudios.</p>

        <div class="cards-grid">
            <?php if ($resultado_prestamos && $resultado_prestamos->num_rows > 0): ?>
                <?php while ($p = $resultado_prestamos->fetch_assoc()): ?>
                    <div class="card">
                        <div>
                            <span class="tag-tipo"><?php echo htmlspecialchars($p['tipo'] ?? 'Crédito Educativo'); ?></span>
                            <h3 class="card-titulo"><?php echo htmlspecialchars($p['nombre']); ?></h3>
                            <p class="card-desc"><?php echo htmlspecialchars(!empty($p['descripcion']) ? $p['descripcion'] : 'Financiación educativa para cubrir tus costos académicos.'); ?></p>
                            
                            <div class="info-box">
                                <div><strong>Monto máx:</strong> <?php echo htmlspecialchars($p['monto_maximo']); ?></div>
                                <div><strong>Tasa de interés:</strong> <?php echo htmlspecialchars($p['tasa_interes']); ?></div>
                            </div>
                        </div>
                        
                        <a href="solicitar-prestamo.php?id=<?php echo $p['id']; ?>" class="btn-solicitar">Solicitar este Préstamo</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; color: #000;">No hay líneas de crédito o préstamos disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="seccion-contacto">
        <p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p>
    </footer>
</body>
</html>