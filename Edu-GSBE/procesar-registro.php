<?php
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_doc   = $_POST['tipo_documento'];
    $num_doc    = $_POST['numero_documento'];
    $nombres    = $_POST['nombres'];
    $apellidos  = $_POST['apellidos'];
    $correo     = $_POST['correo'];
    $telefono   = $_POST['telefono'];
    $direccion  = $_POST['direccion'];
    $estrato    = $_POST['estrato'];
    $institucion= $_POST['institucion'];
    $promedio   = $_POST['promedio'];
    $clave      = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar contraseña por seguridad
    $fecha_subida = date('Y-m-d H:i:s');

    // Procesar la subida de la foto de perfil
    $ruta_bd = "";
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = time() . "_" . basename($_FILES['foto_perfil']['name']);
        $carpeta_destino = "uploads/";
        
        // Crear la carpeta 'uploads' automáticamente si no existe en XAMPP
        if (!file_exists($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }
        
        $ruta_completa = $carpeta_destino . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_completa)) {
            $ruta_bd = $ruta_completa; // Esta ruta es la que se guarda en MySQL
        }
    }

    // Insertar los datos en tu tabla (asegúrate de que los nombres de columnas coincidan con tu base de datos)
    $sql = "INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, correo, telefono, direccion, estrato, institucion, promedio, password, foto_perfil, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssssssdsss", $tipo_doc, $num_doc, $nombres, $apellidos, $correo, $telefono, $direccion, $estrato, $institucion, $promedio, $clave, $ruta_bd, $fecha_subida);
        
        if ($stmt->execute()) {
            echo "<script>
                    alert('¡Estudiante registrado con éxito!');
                    window.location.href = 'login.html';
                  </script>";
        } else {
            echo "Error al registrar en la base de datos: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la consulta: " . $conexion->error;
    }
}
$conexion->close();
?>