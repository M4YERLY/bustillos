<?php
session_start();
include("../../modelo/conexion.php");
include("../../componentes/header.php");

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener las fechas del formulario o asignar valores por defecto
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;

// Consulta SQL para obtener los procesos próximos a culminar según las fechas seleccionadas
$query = "
    SELECT 
        p.id AS proceso_id,
        p.nombre AS proceso_nombre,
        dt.fecha_inicio,
        dt.fecha_fin,
        DATEDIFF(dt.fecha_fin, CURDATE()) AS dias_restantes,
        cl.id AS cliente_id,
        CONCAT(cl.nombre, ' ', cl.apellido_paterno, ' ', cl.apellido_materno) AS cliente_nombre_completo,
        t.id AS tramite_id,
        t.nombre AS tramite_nombre,
        e.nombre AS empleado_responsable
    FROM detalle_tramite dt
    JOIN procesos p ON dt.proceso_id = p.id
    JOIN tramites t ON dt.tramite_id = t.id
    JOIN clientes cl ON t.cliente_id = cl.id
    JOIN empleados e ON dt.empleado_id = e.id
    WHERE 1=1
";

// Agregar filtros de fecha si están definidos
if ($fecha_inicio) {
    $query .= " AND dt.fecha_fin >= :fecha_inicio";
}
if ($fecha_fin) {
    $query .= " AND dt.fecha_fin <= :fecha_fin";
}

$query .= " ORDER BY dt.fecha_fin ASC";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($query);

if ($fecha_inicio) {
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
}
if ($fecha_fin) {
    $stmt->bindParam(':fecha_fin', $fecha_fin);
}
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4 text-center">Procesos Próximos a Culminar</h4>
                
                <!-- Formulario para seleccionar el rango de fechas -->
                <form method="GET" action="">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-50 me-2">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="../pdf/reportePorFecha.php?fecha_inicio=<?= htmlspecialchars($fecha_inicio) ?>&fecha_fin=<?= htmlspecialchars($fecha_fin) ?>" 
                               target="_blank" 
                               class="btn btn-outline-danger w-50">
                                <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Tabla de resultados -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Proceso</th>
                                <th>Fecha de Inicio</th>
                                <th>Fecha de Fin</th>
                                <th>Días Restantes</th>
                                <th>Cliente</th>
                                <th>Trámite</th>
                                <th>Empleado Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($resultados) > 0): ?>
                                <?php foreach ($resultados as $proceso): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($proceso['proceso_nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['fecha_inicio']) ?></td>
                                        <td><?= htmlspecialchars($proceso['fecha_fin']) ?></td>
                                        <td><?= htmlspecialchars($proceso['dias_restantes']) ?></td>
                                        <td><?= htmlspecialchars($proceso['cliente_nombre_completo']) ?></td>
                                        <td><?= htmlspecialchars($proceso['tramite_nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['empleado_responsable']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron procesos en el rango de fechas seleccionado.</td>
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
