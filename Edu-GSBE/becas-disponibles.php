<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Debe iniciar sesión para continuar.'); window.location.href = 'login.html';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Opción - Edu-GSBE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; }
        
        header { background: #fff; border-bottom: 1px solid #dcdde1; }
        .mi-contenedor { display: flex; align-items: center; padding: 10px 20px; background-color: #d6d4d4; }
        .logo { height: 45px; width: auto; }

        .pagina { max-width: 900px; margin: 40px auto; padding: 0 20px; width: 100%; flex-grow: 1; text-align: center; }
        .volver { 
    display: inline-block; 
    margin-bottom: 20px; 
    color: #2a5fb0; 
    text-decoration: none; 
    font-size: 13px; 
    font-weight: 500; 
}
        .volver:hover { text-decoration: underline; }
        
        h1 { color: #14325c; font-size: 28px; margin-bottom: 10px; font-weight: 700; clear: both; }
        .subtitulo { color: #666; font-size: 15px; margin-bottom: 40px; }

        .grid-opciones { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; text-align: left; }
        
        .tarjeta-opcion { background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border-top: 6px solid #005691; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s; }
        .tarjeta-opcion:hover { transform: translateY(-4px); }
        .tarjeta-opcion.prestamo { border-top-color: #e07a5f; }

        .tarjeta-opcion h2 { color: #14325c; font-size: 22px; margin-bottom: 12px; }
        .tarjeta-opcion p { color: #555; font-size: 14px; line-height: 1.6; margin-bottom: 25px; }

        .btn-opcion { display: inline-block; background-color: #2b5ca8; color: #ffffff; text-align: center; padding: 12px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; text-decoration: none; transition: background 0.2s; }
        .btn-opcion.prestamo-btn { background-color: #e07a5f; }
        .btn-opcion:hover { opacity: 0.9; }

        .seccion-contacto { background-color: #1a2938; color: #cbd5e0; padding: 25px 20px; margin-top: 50px; text-align: center; font-size: 12px; }
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
        <a href="panel-estudiante.php" class="volver">&#8249; Volver al Panel Estudiantil</a>
        
        <h1>¿Qué tipo de postulación deseas realizar?</h1>
        <p class="subtitulo">Selecciona una de las dos opciones disponibles para ver sus respectivas modalidades.</p>

        <div class="grid-opciones">
            <!-- TARJETA 1: BECA (Lleva a la lista de becas) -->
            <div class="tarjeta-opcion">
                <div>
                    <h2>🎓 Convocatorias de Becas</h2>
                    <p>Apoyo financiero de excelencia basado en tu rendimiento académico, méritos o condiciones socioeconómicas. Explora las diferentes becas disponibles para postulación.</p>
                </div>
                <a href="lista-becas.php" class="btn-opcion">Ver Becas Disponibles</a>
            </div>

            <!-- TARJETA 2: PRÉSTAMO (Lleva a la lista de préstamos con cuotas e intereses) -->
            <div class="tarjeta-opcion prestamo">
                <div>
                    <h2>🏦 Préstamos y Créditos Educativos</h2>
                    <p>Financiación con tasas de interés preferenciales, plazos en meses y periodos de gracia diseñados para facilitar el pago de tu matrícula.</p>
                </div>
                <a href="prestamos-disponibles.php" class="btn-opcion prestamo-btn">Ver Préstamos Disponibles</a>
            </div>
        </div>
    </main>

    <footer class="seccion-contacto">
        <p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p>
    </footer>

</body>
</html>