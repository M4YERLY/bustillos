<?php
session_start();
include("../../modelo/conexion.php"); // Incluir la conexión a la base de datos
include("../../componentes/header.php"); // Incluir el header

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar(); // Establecer la conexión

// Procesar el formulario al enviar
if (isset($_POST['registrar'])) {
    // Recibir los datos del formulario
    $nombre = htmlspecialchars($_POST['nombre']);
    $apellido_paterno = htmlspecialchars($_POST['apellido_paterno']);
    $apellido_materno = htmlspecialchars($_POST['apellido_materno']);
    $carnet_identidad = htmlspecialchars($_POST['carnet_identidad']);
    $fecha_nacimiento = htmlspecialchars($_POST['fecha_nacimiento']);
    $telefono = htmlspecialchars($_POST['telefono']);
    $direccion = htmlspecialchars($_POST['direccion']);
    $email = htmlspecialchars($_POST['email']);
    $razon_social = htmlspecialchars($_POST['razon_social']);
    $nit = htmlspecialchars($_POST['nit']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar la contraseña

    // Generar el username a partir de nombre y apellido paterno
    $username = strtolower($nombre . '.' . $apellido_paterno);

    // Procesar la subida de la imagen de perfil
    $directorio_imagenes = '../../imagenes/usuarios/';
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        // Guardar la imagen con el nombre del carnet de identidad
        $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $foto_perfil = $directorio_imagenes . $carnet_identidad . '.' . $extension;
        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $foto_perfil);
        $foto_perfil = 'imagenes/usuarios/' . $carnet_identidad . '.' . $extension; // Ruta para guardar en la BD
    } else {
        // Si no se sube una imagen, usar la imagen por defecto
        $foto_perfil = 'imagenes/usuarios/usuariodefault.png';
    }

    try {
        // Iniciar una transacción para insertar en ambas tablas
        $conn->beginTransaction();

        // Insertar en la tabla usuarios
        $stmt1 = $conn->prepare("INSERT INTO usuarios (username, email, password, role) VALUES (:username, :email, :password, 'cliente')");
        $stmt1->bindParam(':username', $username);
        $stmt1->bindParam(':email', $email);
        $stmt1->bindParam(':password', $password);
        $stmt1->execute();
        $usuario_id = $conn->lastInsertId(); // Obtener el ID del usuario insertado

        // Insertar en la tabla clientes
        $stmt2 = $conn->prepare("INSERT INTO clientes 
            (nombre, apellido_paterno, apellido_materno, carnet_identidad, fecha_nacimiento, telefono, direccion, email, razon_social, nit, usuario_id, foto_perfil) 
            VALUES (:nombre, :apellido_paterno, :apellido_materno, :carnet_identidad, :fecha_nacimiento, :telefono, :direccion, :email, :razon_social, :nit, :usuario_id, :foto_perfil)");
        $stmt2->bindParam(':nombre', $nombre);
        $stmt2->bindParam(':apellido_paterno', $apellido_paterno);
        $stmt2->bindParam(':apellido_materno', $apellido_materno);
        $stmt2->bindParam(':carnet_identidad', $carnet_identidad);
        $stmt2->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt2->bindParam(':telefono', $telefono);
        $stmt2->bindParam(':direccion', $direccion);
        $stmt2->bindParam(':email', $email);
        $stmt2->bindParam(':razon_social', $razon_social);
        $stmt2->bindParam(':nit', $nit);
        $stmt2->bindParam(':usuario_id', $usuario_id);
        $stmt2->bindParam(':foto_perfil', $foto_perfil);
        $stmt2->execute();

        // Confirmar la transacción
        $conn->commit();
        echo "<script>window.location.href = 'administradorLista.php?success=1';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>

<!-- Formulario HTML -->
<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cliente</title>
    <link rel="stylesheet" href="../../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <div class="layout-page">
                <div class="container-xxl">
                    <div class="authentication-wrapper authentication-basic container-p-y mx-auto col-md-6">
                        <div class="authentication-inner">
                            <!-- Formulario de registro -->
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="mb-6">Registrar Nuevo Cliente</h4>
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="mb-3"><label>Nombre Completo</label><input type="text" class="form-control" name="nombre" required></div>
                                        <div class="mb-3"><label>Apellido Paterno</label><input type="text" class="form-control" name="apellido_paterno" required></div>
                                        <div class="mb-3"><label>Apellido Materno</label><input type="text" class="form-control" name="apellido_materno" required></div>
                                        <div class="mb-3"><label>Carnet de Identidad</label><input type="text" class="form-control" name="carnet_identidad" required></div>
                                        <div class="mb-3"><label>Fecha de Nacimiento</label><input type="date" class="form-control" name="fecha_nacimiento" required></div>
                                        <div class="mb-3"><label>Teléfono</label><input type="text" class="form-control" name="telefono" required></div>
                                        <div class="mb-3"><label>Dirección</label><input type="text" class="form-control" name="direccion" required></div>
                                        <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="email" required></div>
                                        <div class="mb-3"><label>Razón Social</label><input type="text" class="form-control" name="razon_social" required></div>
                                        <div class="mb-3"><label>NIT</label><input type="text" class="form-control" name="nit" required></div>
                                        <div class="mb-3"><label>Contraseña</label><input type="password" class="form-control" name="password" required></div>
                                        <div class="mb-3"><label>Foto de Perfil</label><input type="file" class="form-control" name="foto_perfil"></div>
                                        <button type="submit" class="btn btn-primary" name="registrar">Registrar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>