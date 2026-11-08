<?php
session_start();

// Proteger la vista: verificar que haya sesión activa mediante el ID de usuario
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
            alert('Debe iniciar sesión para continuar.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

$usuario_id = $_SESSION['usuario_id'];

// Consultar el nombre real del estudiante desde la base de datos
$sql_user = "SELECT nombre, apellido FROM usuarios WHERE id = '$usuario_id'";
$res_user = $conexion->query($sql_user);
$datos_estudiante = ($res_user && $res_user->num_rows > 0) ? $res_user->fetch_assoc() : ['nombre' => 'Estudiante', 'apellido' => ''];
$nombre_estudiante = $datos_estudiante['nombre'] . ' ' . $datos_estudiante['apellido'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Estudiante - Edu-GSBE</title>
    <link href="styles.css" rel="stylesheet">
    <style>
        .barra-estudiante {
            background-color: #005691;
            color: #ffffff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .boton-cerrar {
            background-color: #e07a5f;
            color: #ffffff;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 13px;
            transition: background 0.2s ease;
        }

        .boton-cerrar:hover {
            background-color: #d1664a;
        }
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

    <!-- BARRA SUPERIOR DE BIENVENIDA -->
    <div class="barra-estudiante">
        <div>
            🎓 <strong>Panel Estudiantil</strong> | Bienvenido(a), <?php echo htmlspecialchars($nombre_estudiante); ?>
        </div>
        <a href="logout.php" class="boton-cerrar">Cerrar Sesión</a>
    </div>

    <main>

        <h1 class="titulo-principal-secciones">Gestión de tus Becas y Trámites</h1>

        <!-- ================= SECCIÓN 1: SOLICITAR BECA ================= -->
        <section class="seccion-bloque fondo-claro" style="position: relative; z-index: 50;">
            <div class="contenedor-bloque">
                <div class="info-bloque">
                    <span class="badget-categoria">Postulaciones</span>
                    <h2>Solicitar Beca o Préstamo</h2>
                    <p>Completa el formulario de solicitud en línea, adjunta tus certificados de notas y soportes socioeconómicos para postularte a las becas o créditos activos.</p>
                    
                    <a href="becas-disponibles.php" style="position: relative; z-index: 999; display: inline-block; background-color: #2a5fb0; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; cursor: pointer;">
                        Iniciar Nueva Postulación
                    </a>
                </div>
                <div class="imagen-bloque">
                    <img src="img/becas-estudiantiles.png" alt="Solicitud de Becas" onerror="this.src='img/Solicita.png';">
                </div>
            </div>
        </section>

        <!-- ================= SECCIÓN 2: ESTADO DE SOLICITUD ================= -->
        <section class="seccion-bloque fondo-gris">
            <div class="contenedor-bloque invertido">
                <div class="info-bloque">
                    <span class="badget-categoria">Seguimiento</span>
                    <h2>Estado de tu Solicitud</h2>
                    <p>Consulta en tiempo real el estado de evaluación de tu trámite, las observaciones emitidas por el comité y la resolución de adjudicación.</p>
                    <a href="estado-solicitud.php" class="boton-seccion secundario">Consultar Mi Estado</a>
                </div>
                <div class="imagen-bloque">
                    <img src="img/estado2.png" alt="Estado de Solicitud" onerror="this.src='img/Solicita.png';">
                </div>
            </div>
        </section>

        <!-- ================= SECCIÓN 3: MI PRÉSTAMO ================= -->
        <section class="seccion-bloque fondo-claro">
            <div class="contenedor-bloque ">
                <div class="info-bloque">
                    <span class="badget-categoria">Financiación</span>
                    <h2>Mi Préstamo Educativo</h2>
                    <p>Revisa el historial de giros, fechas estimadas de desembolso, saldo pendiente y condiciones de tu crédito o financiamiento.</p>
                    <a href="detalle-prestamo.php" style="display: inline-block; background-color: #e07a5f; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; cursor: pointer;">
                        Ver Detalle de Préstamo
                    </a>
                </div>
                <div class="imagen-bloque">
                    <img src="img/prestamo.png" alt="Información de Préstamo" onerror="this.src='img/Solicita.png';">
                </div>
            </div>
        </section>

        <!-- TARJETA 4: REPORTES -->
        <section class="seccion-bloque fondo-claro">
            <div class="contenedor-bloque invertido">
                <div class="info-bloque">
                    <span class="badget-categoria">Reportes</span>
                    <h2>Reportes</h2>
                    <p>Estadísticas generales e historial completo del proceso de postulaciones.</p>
                    <a href="reporte-estudiante.php" style="display: inline-block; background-color: #e07a5f; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; cursor: pointer;">
                        Ver Reportes
                    </a>
                </div>
                <div class="imagen-bloque">
                    <img src="img/reportes.png" alt="Información de Préstamo" onerror="this.src='img/Solicita.png';">
                </div>
            </div>
        </section>
    </main>

    <!-- ================= PIE DE PÁGINA ================= -->
    <footer class="seccion-contacto">
        <div class="contenedor-contacto">
            <div class="encabezado-contacto">
                <h2>Edu-GSBE</h2>
                <img src="img/logotipo.png" alt="Logo Edu-GSBE" class="logo-contacto">
            </div>

            <div class="info-contacto">
                <p><strong>Dirección sede principal:</strong> Carrera 3 No. 18-32, Bogotá D.C., Colombia</p>
                <p><strong>Horario de atención:</strong> Lunes a viernes de 7:30 a.m. a 5:30 p.m.</p>
                <p><strong>Atención al usuario / Chat:</strong> Domingo a domingo, 24 horas</p>
                <p><strong>Línea Gratuita Nacional:</strong> 018000-916821</p>
                <p><strong>Correo de notificaciones:</strong> contacto@edu-gsbe.gov.co</p>
            </div>

            <hr class="divisor-contacto">

            <div class="redes-contacto">
                <a href="https://www.facebook.com/FBBecasColombia"><img src="img/facebook.png" alt="facebook">Edu-GSBE Colombia</a><br>
                <a href="https://x.com/BecasColombia_"><img src="img/x.png" alt="X">@Edu-GSBE</a><br>
                <a href="https://www.instagram.com/becascolombia/"><img src="img/instagram.png" alt="instagram">EDU-GSBE_Colombia</a><br>
            </div>

            <div class="enlaces-legales">
                <a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=49981">Políticas de privacidad</a>
                <a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=227390">Términos y condiciones</a>
            </div>
        </div>
    </footer>

</body>
</html>