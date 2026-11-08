<?php
session_start();
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Lógica para publicar nueva convocatoria
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['publicar'])) {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $tipo = $conexion->real_escape_string($_POST['tipo']);
    $cobertura = $conexion->real_escape_string($_POST['cobertura']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);

    if (stripos($tipo, 'Crédito') !== false || stripos($tipo, 'Préstamo') !== false) {
        $tasa_interes = isset($_POST['tasa_interes']) && !empty($_POST['tasa_interes']) ? $conexion->real_escape_string($_POST['tasa_interes']) : '1.0% M.V.';
        
        $monto_limpio = str_replace('Monto máx:', '', $cobertura);
        $monto_final = "Monto máx: " . trim($monto_limpio);

        $conexion->query("INSERT INTO prestamos (nombre, tipo, monto_maximo, tasa_interes, estado) VALUES ('$nombre', '$tipo', '$monto_final', '$tasa_interes', 'activa')");
    } else {
        $promedio = isset($_POST['promedio']) && !empty($_POST['promedio']) ? $conexion->real_escape_string($_POST['promedio']) : '3.50';
        
        $conexion->query("INSERT INTO becas (nombre, descripcion, cobertura, promedio_minimo, estado) VALUES ('$nombre', '$descripcion', '$cobertura', '$promedio', 'activa')");
    }
    header("Location: gestionar-becas.php");
    exit();
}

// Consultar becas y préstamos
$resultado_becas = $conexion->query("SELECT * FROM becas ORDER BY id ASC");
$resultado_prestamos = $conexion->query("SELECT * FROM prestamos ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Convocatorias (Becas y Préstamos)</title>
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

        .form-container { background: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); padding: 25px; margin-bottom: 40px; border: 1px solid #e2e8f0; }
        .form-container h3 { color: #1e3a8a; font-size: 18px; margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 13px; font-weight: bold; color: #000; margin-bottom: 6px; }
        .form-group input, .form-group select, .form-group textarea { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #000; background: #fff; }
        
        .btn-publicar { background-color: #16a34a; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-publicar:hover { background-color: #15803d; }

        .tabla-container { background: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        th, td { padding: 14px 18px; border-bottom: 1px solid #cbd5e1; color: #000; vertical-align: middle; }
        th { background-color: #0f766e; color: #fff; font-weight: bold; }
        
        .btn-eliminar { background-color: #dc2626; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-eliminar:hover { background-color: #b91c1c; }

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
        <h1>Gestión de Convocatorias (Becas y Préstamos)</h1>
        
        <!-- FORMULARIO -->
        <div class="form-container">
            <h3>Crear Nueva Convocatoria</h3>
            <form action="gestionar-becas.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre de la Convocatoria</label>
                        <input type="text" name="nombre" placeholder="Ej: Beca Talento TI" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo" id="select-tipo" onchange="cambiarCampoDinamico()" required>
                            <option value="Beca Institucional">Beca Institucional</option>
                            <option value="Beca Socioeconómica">Beca Socioeconómica</option>
                            <option value="Beca Mérito Extracurricular">Beca Mérito Extracurricular</option>
                            <option value="Beca Académica Avanzada">Beca Académica Avanzada</option>
                            <option value="Beca Movilidad">Beca Movilidad</option>
                            <option value="Crédito a Largo Plazo">Crédito a Largo Plazo</option>
                            <option value="Crédito a Corto Plazo">Crédito a Corto Plazo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cobertura / Monto</label>
                        <input type="text" name="cobertura" placeholder="Ej: $10.000.000 COP o 100% de la matrícula" required>
                    </div>
                    
                    <!-- Campo Promedio -->
                    <div class="form-group" id="grupo-promedio">
                        <label>Promedio Mínimo Requerido</label>
                        <input type="text" name="promedio" placeholder="3,50">
                    </div>

                    <!-- Campo Tasa de Interés -->
                    <div class="form-group" id="grupo-tasa" style="display: none;">
                        <label>Tasa de Interés</label>
                        <input type="text" name="tasa_interes" placeholder="Ej: 0.9% M.V.">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Descripción detallada</label>
                        <input type="text" name="descripcion" placeholder="Describe brevemente los beneficios y requisitos..." required>
                    </div>
                </div>
                <button type="submit" name="publicar" class="btn-publicar">Publicar Convocatoria</button>
            </form>
        </div>

        <!-- TABLA 1: BECAS -->
        <h2>Convocatorias de Becas Registradas</h2>
        <div class="tabla-container">
            <?php if ($resultado_becas && $resultado_becas->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Cobertura</th>
                            <th>Promedio Mín.</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultado_becas->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $row['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($row['cobertura']); ?></td>
                                <td><strong><?php echo $row['promedio_minimo']; ?></strong></td>
                                <td><span style="color: #16a34a; font-weight: bold;"><?php echo ucfirst(!empty($row['estado']) ? $row['estado'] : 'Activa'); ?></span></td>
                                <td>
                                    <a href="eliminar-convocatoria.php?id=<?php echo $row['id']; ?>&tipo=beca" class="btn-eliminar" onclick="return confirm('¿Está seguro de eliminar esta beca?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="sin-resultados">No hay convocatorias de becas registradas actualmente.</div>
            <?php endif; ?>
        </div>

        <!-- TABLA 2: CRÉDITOS / PRÉSTAMOS -->
        <h2>Convocatorias de Créditos o Préstamos Registrados</h2>
        <div class="tabla-container">
            <?php if ($resultado_prestamos && $resultado_prestamos->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Crédito / Préstamo</th>
                            <th>Tipo</th>
                            <th>Monto Máximo</th>
                            <th>Tasa de Interés</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_p = $resultado_prestamos->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $row_p['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($row_p['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row_p['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($row_p['monto_maximo']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row_p['tasa_interes']); ?></strong></td>
                                <td><span style="color: #16a34a; font-weight: bold;"><?php echo ucfirst(!empty($row_p['estado']) ? $row_p['estado'] : 'Activa'); ?></span></td>
                                <td>
                                    <a href="eliminar-convocatoria.php?id=<?php echo $row_p['id']; ?>&tipo=prestamo" class="btn-eliminar" onclick="return confirm('¿Está seguro de eliminar este préstamo?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="sin-resultados">No hay convocatorias de créditos o préstamos registradas actualmente.</div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="seccion-contacto"><p>&copy; Edu-GSBE. Sistema de Gestión y Solicitud de Becas Estudiantiles.</p></footer>

    <script>
        function cambiarCampoDinamico() {
            var selectTipo = document.getElementById('select-tipo').value;
            var grupoPromedio = document.getElementById('grupo-promedio');
            var grupoTasa = document.getElementById('grupo-tasa');

            if (selectTipo.includes('Crédito') || selectTipo.includes('Préstamo')) {
                grupoPromedio.style.display = 'none';
                grupoTasa.style.display = 'flex';
            } else {
                grupoPromedio.style.display = 'flex';
                grupoTasa.style.display = 'none';
            }
        }
    </script>
</body>
</html>