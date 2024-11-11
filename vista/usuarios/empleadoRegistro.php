<?php
ob_start();
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
    $username = htmlspecialchars($_POST['username']);
    $salario = htmlspecialchars($_POST['salario']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar la contraseña

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
        $stmt1 = $conn->prepare("INSERT INTO usuarios (username, email, password, role) VALUES (:username, :email, :password, 'empleado')");
        $stmt1->bindParam(':username', $username);
        $stmt1->bindParam(':email', $email);
        $stmt1->bindParam(':password', $password);
        $stmt1->execute();
        $usuario_id = $conn->lastInsertId(); // Obtener el ID del usuario insertado

        // Insertar en la tabla empleados
        $stmt2 = $conn->prepare("INSERT INTO empleados 
            (nombre, apellido_paterno, apellido_materno, carnet_identidad, fecha_nacimiento, telefono, direccion, email, salario, usuario_id, foto_perfil) 
            VALUES (:nombre, :apellido_paterno, :apellido_materno, :carnet_identidad, :fecha_nacimiento, :telefono, :direccion, :email, :salario, :usuario_id, :foto_perfil)");
        $stmt2->bindParam(':nombre', $nombre);
        $stmt2->bindParam(':apellido_paterno', $apellido_paterno);
        $stmt2->bindParam(':apellido_materno', $apellido_materno);
        $stmt2->bindParam(':carnet_identidad', $carnet_identidad);
        $stmt2->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt2->bindParam(':telefono', $telefono);
        $stmt2->bindParam(':direccion', $direccion);
        $stmt2->bindParam(':email', $email);
        $stmt2->bindParam(':salario', $salario);
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

<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleado</title>
    <link rel="stylesheet" href="../../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="../../assets/css/demo.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
                                    <h4 class="mb-6">Registrar Nuevo Empleado</h4>

                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre Completo</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                                            <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                            <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="carnet_identidad" class="form-label">Carnet de Identidad</label>
                                            <input type="text" class="form-control" id="carnet_identidad" name="carnet_identidad" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="telefono" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="direccion" class="form-label">Dirección</label>
                                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="salario" class="form-label">Salario</label>
                                            <input type="number" class="form-control" id="salario" name="salario" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Nombre de Usuario</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Contraseña</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="foto_perfil" class="form-label">Foto de Perfil</label>
                                            <input type="file" class="form-control" id="foto_perfil" name="foto_perfil">
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary" name="registrar">Registrar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- / Layout container -->
        </div>
    </div>

    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../assets/vendor/js/menu.js"></script>
    <script src="../../assets/vendor/js/helpers.js"></script>
    <script src="../../assets/js/config.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>

</html>
