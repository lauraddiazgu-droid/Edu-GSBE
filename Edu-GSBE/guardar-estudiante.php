<?php
// Conexión a la base de datos de XAMPP
$servidor   = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "edugsbe_db";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Validar OBLIGATORIEDAD de la foto de perfil en el servidor
    if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== 0) {
        echo "<script>
                alert('Es obligatorio subir una foto de perfil para registrarse.');
                window.history.back();
              </script>";
        exit();
    }

    $tipo_doc    = $_POST['tipo_documento'];
    $documento   = $_POST['documento'];
    $nombre      = $_POST['nombre'];
    $apellido    = $_POST['apellido'];
    $email       = $_POST['email'];
    $telefono    = $_POST['telefono'];
    $direccion   = $_POST['direccion'];
    $institucion = $_POST['institucion'];
    $promedio    = $_POST['promedio'];
    $estrato     = $_POST['estrato'];
    $pass_hash   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 2. Procesar y guardar la imagen usando NOMBRE y APELLIDO del estudiante
    $carpeta_destino = "uploads/";
    if (!file_exists($carpeta_destino)) {
        mkdir($carpeta_destino, 0777, true);
    }

    // Limpia caracteres especiales y acentos para evitar problemas en el sistema de archivos
    $nombre_limpio   = preg_replace("/[^a-zA-Z0-9_-]/", "", str_replace(' ', '_', $nombre));
    $apellido_limpio = preg_replace("/[^a-zA-Z0-9_-]/", "", str_replace(' ', '_', $apellido));

    $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
    
    // Formato final del archivo: Nombre_Apellido_Documento.extension
    $nombre_archivo = $nombre_limpio . "_" . $apellido_limpio . "_" . $documento . "." . $extension;
    $ruta_foto = $carpeta_destino . $nombre_archivo;

    if (!move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_foto)) {
        echo "<script>
                alert('Error al guardar la foto de perfil en el servidor.');
                window.history.back();
              </script>";
        exit();
    }

    // 3. Validar duplicados por correo o número de documento
    $check_usuario = "SELECT id FROM usuarios WHERE email = '$email' OR documento = '$documento'";
    $resultado = $conexion->query($check_usuario);

    if ($resultado && $resultado->num_rows > 0) {
        echo "<script>
                alert('El correo electrónico o número de documento ya se encuentra registrado.');
                window.history.back();
              </script>";
    } else {
        // 4. Insertar en la tabla 'usuarios'
        $sql_usuario = "INSERT INTO usuarios (documento, tipo_documento, nombre, apellido, email, password, rol) 
                        VALUES ('$documento', '$tipo_doc', '$nombre', '$apellido', '$email', '$pass_hash', 'estudiante')";

        if ($conexion->query($sql_usuario) === TRUE) {
            $usuario_id = $conexion->insert_id;

            // 5. Insertar en 'perfil_estudiante' guardando la ruta con el nombre
            $sql_perfil = "INSERT INTO perfil_estudiante (usuario_id, telefono, direccion, promedio_acumulado, institucion_educativa, estrato, foto) 
                           VALUES ('$usuario_id', '$telefono', '$direccion', '$promedio', '$institucion', '$estrato', '$ruta_foto')";

            if ($conexion->query($sql_perfil) === TRUE) {
                echo "<script>
                        alert('¡Estudiante registrado con éxito!');
                        window.location.href = 'login.html';
                      </script>";
            } else {
                echo "Error al registrar el perfil: " . $conexion->error;
            }
        } else {
            echo "Error al registrar el usuario: " . $conexion->error;
        }
    }

    $conexion->close();
}
?>