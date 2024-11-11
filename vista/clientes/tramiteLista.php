<?php
session_start();
include("../../modelo/conexion.php");

// Verificar si el usuario ha iniciado sesión y si es cliente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener el usuario de la sesión
$username = $_SESSION['usuario'];

// Consultar la información del cliente para obtener su ID
$stmt = $conn->prepare("
    SELECT c.id
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

// Obtener los trámites del cliente
$stmt_tramites = $conn->prepare("
    SELECT t.id, t.nombre, t.descripcion, t.fecha, t.tipo, e.nombre AS estado
    FROM tramites t
    JOIN estado_tramite e ON t.estado_id = e.id
    WHERE t.cliente_id = :cliente_id
");
$stmt_tramites->bindParam(':cliente_id', $cliente['id']);
$stmt_tramites->execute();
$tramites = $stmt_tramites->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('../../componentes/headerCliente.php'); ?>

<div class="container-xxl">
    <div class="row justify-content-center mt-5">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-center">Lista de Trámites</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre del Trámite</th>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>ACCIONES</th> <!-- Nueva columna para acciones -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tramites as $index => $tramite): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($tramite['nombre']) ?></td>
                                        <td><?= htmlspecialchars($tramite['descripcion']) ?></td>
                                        <td><?= htmlspecialchars($tramite['fecha']) ?></td>
                                        <td><?= htmlspecialchars($tramite['tipo']) ?></td>
                                        <td><?= htmlspecialchars($tramite['estado']) ?></td>
                                        <td>
                                            <!-- Botón para ver procesos -->
                                            <a href="procesoClienteLista.php?id_tramite=<?= htmlspecialchars($tramite['id']) ?>" class="btn btn-primary">Ver Procesos</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($tramites)): ?>
                        <p class="text-center mt-3">No se encontraron trámites asociados a este cliente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/vendor/js/bootstrap.js"></script>
<script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="../../assets/vendor/js/menu.js"></script>
<script src="../../assets/js/main.js"></script>
</body>
</html>
