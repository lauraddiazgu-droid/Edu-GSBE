<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    echo "<script>
            alert('Acceso denegado. Debe iniciar sesión como Administrador.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

$email_admin = isset($_SESSION['email']) ? $_SESSION['email'] : 'administrador@ecci.edu.co';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Edu-GSBE</title>
    <link href="styles.css" rel="stylesheet">
    <style>
        .barra-admin {
            background-color: #1c3144;
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

        /* GRID Y TARJETAS UNIFORMES DE ADMINISTRACIÓN */
        .grid-admin {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .tarjeta-admin {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tarjeta-admin:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .icono-tarjeta {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .tarjeta-admin h3 {
            color: #005691;
            font-size: 17px;
            margin-bottom: 10px;
        }

        .tarjeta-admin p {
            font-size: 12px;
            color: #666666;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <header>
        <div class="mi-contenedor">
            <a href="panel-admin.php">
                <img src="img/logotipo.png" alt="Edu-GSBE" class="logo">
            </a>
        </div>
    </header>

    <!-- BARRA SUPERIOR DE BIENVENIDA ADMINISTRATIVA -->
    <div class="barra-admin">
        <div>
            🛠️ <strong>Panel Administrativo Edu-GSBE</strong> | Usuario: <?php echo htmlspecialchars($email_admin); ?>
        </div>
        <a href="logout.php" class="boton-cerrar">Cerrar Sesión</a>
    </div>

    <main>
        <h1 class="titulo-principal-secciones" style="text-align: center; margin-top: 40px; color: #005691;">
            GESTIÓN DEL SISTEMA
        </h1>

        <!-- CUADRÍCULA UNIFORME DE MÓDULOS ADMINISTRATIVOS -->
        <div class="grid-admin">
            
            <!-- TARJETA 1: EVALUAR SOLICITUDES -->
            <a href="evaluar-solicitudes.php" class="tarjeta-admin">
                <img src="img/evaluar.png" alt="Evaluar Solicitudes" class="icono-tarjeta" onerror="this.src='img/estado.png';">
                <h3>Evaluar Solicitudes</h3>
                <p>Revisión, aprobación y rechazo de postulaciones presentadas por estudiantes.</p>
            </a>

            <!-- TARJETA 2: GESTIONAR BECAS -->
            <a href="gestionar-becas.php" class="tarjeta-admin">
                <img src="img/becas-dis.png" alt="Gestionar Becas" class="icono-tarjeta" onerror="this.src='img/Solicita.png';">
                <h3>Gestionar Becas</h3>
                <p>Configuración de requisitos, cupos y convocatorias activas en el sistema.</p>
            </a>

            <!-- TARJETA 3: REGISTRAR PAGO -->
            <a href="registrar-pago.php" class="tarjeta-admin">
                <img src="img/prestamo.png" alt="Registrar Pago" class="icono-tarjeta" onerror="this.src='img/Solicita.png';">
                <h3>Registrar Pago</h3>
                <p>Control de desembolsos, planes de financiación y giros realizados.</p>
            </a>

            <!-- TARJETA 4: REPORTES (Abre el nuevo archivo de bandeja de reportes) -->
            <a href="ver-reportes.php" class="tarjeta-admin">
                <img src="img/reportes.png" alt="Reportes" class="icono-tarjeta" onerror="this.src='img/Solicita.png';">
                <h3>Reportes</h3>
                <p>Consulta la bandeja de reportes, quejas y sugerencias enviadas por los usuarios.</p>
            </a>

        </div>
    </main>

    <!-- PIE DE PÁGINA INSTITUCIONAL -->
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
                <p><strong>Call Center / PBX:</strong> +57 (601) 352-6600</p>
                <p><strong>Correo de notificaciones:</strong> contacto@edu-gsbe.gov.co</p>
            </div>

            <hr class="divisor-contacto">

            <div class="redes-contacto" style="text-align: center; margin-bottom: 15px;">
                <a href="https://www.facebook.com/FBBecasColombia" style="margin: 0 10px; color: #005691; text-decoration: none; font-size: 12px; font-weight: bold;">Edu-GSBE Colombia</a>
                <a href="https://x.com/BecasColombia_" style="margin: 0 10px; color: #005691; text-decoration: none; font-size: 12px; font-weight: bold;">@Edu-GSBE</a>
                <a href="https://www.instagram.com/becascolombia/" style="margin: 0 10px; color: #005691; text-decoration: none; font-size: 12px; font-weight: bold;">EDU-GSBE_Colombia</a>
            </div>

            <div class="enlaces-legales">
                <a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=49981">Políticas de privacidad</a>
                <a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=227390">Términos y condiciones</a>
            </div>
        </div>
    </footer>

</body>
</html>