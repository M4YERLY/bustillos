<?php
ob_start();
session_start();
include("../../modelo/conexion.php"); // Conexión a la base de datos
include("../../componentes/header.php"); // Header

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

$conexion = new Conexion();
$conn = $conexion->conectar();

// Funcionalidad de búsqueda
$busqueda = $_GET['busqueda'] ?? '';

// Consulta para obtener todos los procesos
$query = "
    SELECT CONCAT(c.nombre, ' ', c.apellido_paterno) AS cliente_nombre_completo,
           t.nombre AS tramite_nombre,
           p.nombre AS proceso_nombre,
           p.tipo AS proceso_tipo,
           ep.nombre AS estado_nombre,
           ep.id AS estado_id,
           e.nombre AS empleado_nombre,
           t.id AS tramite_id,
           c.id AS cliente_id,
           p.id AS proceso_id
    FROM procesos p
    JOIN tramites t ON p.tramite_id = t.id
    JOIN clientes c ON t.cliente_id = c.id
    JOIN detalle_tramite dt ON p.id = dt.proceso_id
    JOIN empleados e ON dt.empleado_id = e.id
    JOIN estado_proceso ep ON dt.estado_id = ep.id
";

// Filtrar por búsqueda
if (!empty($busqueda)) {
    $query .= " WHERE p.nombre LIKE :busqueda OR c.nombre LIKE :busqueda OR c.apellido_paterno LIKE :busqueda";
}

$stmt = $conn->prepare($query);

// Bind del parámetro de búsqueda
if (!empty($busqueda)) {
    $busqueda_param = '%' . $busqueda . '%';
    $stmt->bindParam(':busqueda', $busqueda_param);
}

$stmt->execute();
$procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Custom CSS -->
<style>
    .estado-terminado {
        background-color: #28a745; /* Verde */
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
    }

    .estado-en-proceso {
        background-color: #ffc107; /* Amarillo */
        color: #212529;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
    }

    .estado-sin-procesar {
        background-color: #dc3545; /* Rojo */
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
    }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-lg">
            <div class="card-body">
                <h2 class="text-primary text-center mt-4">
                    <i class="bi bi-list-check"></i> Lista de Seguimientos
                </h2>

                <!-- Barra de búsqueda -->
                <div class="d-flex justify-content-between mb-4">
                    <form method="GET" action="" class="d-flex">
                        <input type="text" name="busqueda" class="form-control me-2" placeholder="Buscar procesos..." value="<?= htmlspecialchars($busqueda) ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </form>
                    <a href="../pdf/procesoLista.php" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                    </a>
                </div>

                <!-- Tabla de procesos -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Cliente</th>
                                <th>Trámite</th>
                                <th>Proceso</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Empleado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($procesos) > 0): ?>
                                <?php foreach ($procesos as $proceso): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($proceso['cliente_nombre_completo']) ?></td>
                                        <td><?= htmlspecialchars($proceso['tramite_nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['proceso_nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['proceso_tipo']) ?></td>
                                        <td>
                                            <span class="
                                                <?php 
                                                    if ($proceso['estado_id'] == 1) echo 'estado-terminado'; 
                                                    elseif ($proceso['estado_id'] == 2) echo 'estado-en-proceso'; 
                                                    elseif ($proceso['estado_id'] == 3) echo 'estado-sin-procesar'; 
                                                ?>">
                                                <?= htmlspecialchars(ucwords($proceso['estado_nombre'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($proceso['empleado_nombre']) ?></td>
                                        <td>
                                            <a href="proceso<?= htmlspecialchars($proceso['proceso_tipo']) ?>Detalles.php?proceso_id=<?= $proceso['proceso_id'] ?>&tramite_id=<?= $proceso['tramite_id'] ?>&cliente_id=<?= $proceso['cliente_id'] ?>" class="btn btn-info btn-sm">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                            <a href="procesoEstadoEdicion.php?proceso_id=<?= $proceso['proceso_id'] ?>&tramite_id=<?= $proceso['tramite_id'] ?>&cliente_id=<?= $proceso['cliente_id'] ?>" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron procesos.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../../componentes/footer.php"); ?>
