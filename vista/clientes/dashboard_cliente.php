<?php
ob_start();
session_start();
include("../../modelo/conexion.php");
include("../../componentes/headerCliente.php"); // Incluir el header***********************************

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener el usuario de la sesión
$username = $_SESSION['usuario'];

// Consultar información del cliente
$stmt = $conn->prepare("
    SELECT c.nombre, c.apellido_paterno, c.apellido_materno, c.email, c.carnet_identidad, c.fecha_nacimiento, c.telefono, c.direccion
    FROM clientes c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE u.username = :username
");
$stmt->bindParam(':username', $username);
$stmt->execute();
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no encuentra al cliente, redirigir al login
if (!$cliente) {
    header("Location: login.php");
    exit();
}
?>
<div class="container-fluid">
    <div class="row justify-content-center mt-5">
        <div class="col-lg-8 col-md-10 col-sm-12">
            <div class="card shadow-sm rounded">
                <div class="card-body">
                    <h4 class="card-title text-center">Perfil del Cliente</h4>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4"><strong>Nombre:</strong></div>
                        <div class="col-12 col-md-8 text-sm-start"><?= htmlspecialchars($cliente['nombre']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4"><strong>Apellido Paterno:</strong></div>
                        <div class="col-12 col-md-8 text-sm-start"><?= htmlspecialchars($cliente['apellido_paterno']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4"><strong>Apellido Materno:</strong></div>
                        <div class="col-12 col-md-8 text-sm-start"><?= htmlspecialchars($cliente['apellido_materno']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4"><strong>Email:</strong></div>
                        <div class="col-12 col-md-8 text-sm-start"><?= htmlspecialchars($cliente['email']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4"><strong>Carnet de Identidad:</strong></div>
                        <div class="col-12 col-md-8 text-sm-start"><?= htmlspecialchars($cliente['carnet_identidad']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4"><strong>Fecha de Nacimiento:</strong></div>
                        <div class="col-12 col-md-8 text-sm-start"><?= htmlspecialchars($cliente['fecha_nacimiento']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4"><strong>Teléfono:</strong></div>
                        <div class="col-12 col-md-8 text-sm-start"><?= htmlspecialchars($cliente['telefono']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4"><strong>Dirección:</strong></div>
                        <div class="col-12 col-md-8 text-sm-start"><?= htmlspecialchars($cliente['direccion']) ?></div>
                    </div>
                </div>
            </div>
        </div>
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