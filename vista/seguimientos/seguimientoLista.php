<?php
include("../../componentes/rol.php");
include("../../componentes/header.php");

// Verificar el rol del usuario actual
$username = $_SESSION['usuario'] ?? null;

if (!$username) {
    header("Location: login.php");
    exit();
}

$role = getUserRole($username);

$conexion = new Conexion();
$conn = $conexion->conectar();

// Consulta para obtener todos los seguimientos
$query = "
    SELECT s.id, t.nombre AS tramite_nombre, s.observaciones, s.fecha_seguimiento, e.nombre AS empleado_nombre, s.tipo_seguimiento
    FROM seguimientos s
    JOIN tramites t ON s.tramite_id = t.id
    JOIN empleados e ON s.empleado_id = e.id
";

$stmt = $conn->prepare($query);
$stmt->execute();
$seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar la eliminación de un seguimiento
if (isset($_GET['eliminar'])) {
    $seguimiento_id = $_GET['eliminar'];
    $stmt_eliminar = $conn->prepare("DELETE FROM seguimientos WHERE id = :id");
    $stmt_eliminar->bindParam(':id', $seguimiento_id);
    $stmt_eliminar->execute();
    header("Location: seguimientoLista.php");
    exit();
}
?>
<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-lg">
            <div class="card-body">
                <h2 class="text-primary text-center mt-4">Lista de Seguimientos</h2>

                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mb-4">
                    <a href="seguimientoRegistro.php" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle"></i> Agregar Seguimiento
                    </a>

                    <a href="../pdf/seguimientoLista.php" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Generar Reporte
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Trámite</th>
                                <th>Observaciones</th>
                                <th>Fecha de Seguimiento</th>
                                <th>Empleado</th>
                                <th>Tipo de Seguimiento</th>
                                <!-- Usuarios (solo para admin) -->
                                <?php if ($role === 'admin'): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($seguimientos as $seguimiento): ?>
                                <tr>
                                    <td><?= htmlspecialchars($seguimiento['tramite_nombre']) ?></td>
                                    <td><?= htmlspecialchars($seguimiento['observaciones']) ?></td>
                                    <td><?= htmlspecialchars($seguimiento['fecha_seguimiento']) ?></td>
                                    <td><?= htmlspecialchars($seguimiento['empleado_nombre']) ?></td>
                                    <td><?= htmlspecialchars($seguimiento['tipo_seguimiento']) ?></td>
                                    <!-- Usuarios (solo para admin) -->
                                    <?php if ($role === 'admin'): ?>
                                        <td>

                                            <a href="seguimientoEdicion.php?id=<?= $seguimiento['id'] ?>" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>

                                            <a href="seguimientoEliminacion.php?id=<?= $seguimiento['id'] ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Estás seguro de eliminar este seguimiento?')">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../../componentes/footer.php"); ?>