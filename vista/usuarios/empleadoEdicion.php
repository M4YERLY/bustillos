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
$conn = $conexion->conectar();

// Verificar si se ha enviado un ID para editar
if (isset($_GET['id'])) {
    $id = htmlspecialchars($_GET['id']);

    // Obtener los datos del empleado
    $stmt = $conn->prepare("SELECT e.*, u.username FROM empleados e JOIN usuarios u ON e.usuario_id = u.id WHERE e.id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el empleado existe
    if (!$empleado) {
        echo "No se encontró el empleado.";
        exit();
    }
}

// Procesar el formulario de edición
if (isset($_POST['editar'])) {
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

    // Procesar la subida de una nueva imagen de perfil (si se proporciona)
    $directorio_imagenes = '../../imagenes/usuarios/';
    $foto_perfil = $empleado['foto_perfil']; // Mantener la imagen actual si no se sube una nueva

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $foto_perfil = $directorio_imagenes . $carnet_identidad . '.' . $extension;
        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $foto_perfil);
        $foto_perfil = 'imagenes/usuarios/' . $carnet_identidad . '.' . $extension; // Ruta para guardar en la BD
    }

    try {
        // Actualizar la tabla usuarios
        $stmt1 = $conn->prepare("UPDATE usuarios SET username = :username, email = :email WHERE id = :usuario_id");
        $stmt1->bindParam(':username', $username);
        $stmt1->bindParam(':email', $email);
        $stmt1->bindParam(':usuario_id', $empleado['usuario_id']);
        $stmt1->execute();

        // Actualizar la tabla empleados
        $stmt2 = $conn->prepare("UPDATE empleados 
            SET nombre = :nombre, apellido_paterno = :apellido_paterno, apellido_materno = :apellido_materno, 
                carnet_identidad = :carnet_identidad, fecha_nacimiento = :fecha_nacimiento, telefono = :telefono, 
                direccion = :direccion, email = :email, salario = :salario, foto_perfil = :foto_perfil 
            WHERE id = :id");
        $stmt2->bindParam(':nombre', $nombre);
        $stmt2->bindParam(':apellido_paterno', $apellido_paterno);
        $stmt2->bindParam(':apellido_materno', $apellido_materno);
        $stmt2->bindParam(':carnet_identidad', $carnet_identidad);
        $stmt2->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt2->bindParam(':telefono', $telefono);
        $stmt2->bindParam(':direccion', $direccion);
        $stmt2->bindParam(':email', $email);
        $stmt2->bindParam(':salario', $salario);
        $stmt2->bindParam(':foto_perfil', $foto_perfil);
        $stmt2->bindParam(':id', $id);
        $stmt2->execute();

        // Redirigir a la lista después de la actualización
        echo "<script>window.location.href = 'administradorLista.php?success=2';</script>";
        exit();
    } catch (Exception $e) {
        echo "Error al actualizar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado</title>
    <link rel="stylesheet" href="../../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="../../assets/css/demo.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <div class="layout-page">
                <div class="container-xxl">
                    <div class="authentication-wrapper authentication-basic container-p-y mx-auto col-md-6">
                        <div class="authentication-inner">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="mb-6">Editar Empleado</h4>

                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre Completo</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($empleado['nombre']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                                            <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?= htmlspecialchars($empleado['apellido_paterno']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                            <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?= htmlspecialchars($empleado['apellido_materno']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="carnet_identidad" class="form-label">Carnet de Identidad</label>
                                            <input type="text" class="form-control" id="carnet_identidad" name="carnet_identidad" value="<?= htmlspecialchars($empleado['carnet_identidad']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($empleado['fecha_nacimiento']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="telefono" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($empleado['telefono']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="direccion" class="form-label">Dirección</label>
                                            <input type="text" class="form-control" id="direccion" name="direccion" value="<?= htmlspecialchars($empleado['direccion']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($empleado['email']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="salario" class="form-label">Salario</label>
                                            <input type="text" class="form-control" id="salario" name="salario" value="<?= htmlspecialchars($empleado['salario']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Nombre de Usuario</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($empleado['username']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="foto_perfil" class="form-label">Foto de Perfil Actual</label><br>
                                            <img src="../../<?= htmlspecialchars($empleado['foto_perfil']) ?>" alt="Foto de Perfil" class="profile-img"><br><br>
                                            <label for="foto_perfil" class="form-label">Cambiar Foto de Perfil</label>
                                            <input type="file" class="form-control" id="foto_perfil" name="foto_perfil">
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary" name="editar">Actualizar</button>
                                            <a href="administradorLista.php" class="btn btn-secondary">Cancelar</a>
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
