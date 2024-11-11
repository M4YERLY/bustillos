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

// Obtener todos los estados disponibles
$stmt_estados = $conn->prepare("SELECT id, nombre FROM estado_tramite");
$stmt_estados->execute();
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);

// Obtener trámites según el estado seleccionado
$estado_id = $_GET['estado_id'] ?? null;

$query = "
    SELECT 
        t.id AS tramite_id,
        t.nombre AS tramite_nombre,
        t.descripcion,
        t.fecha,
        et.nombre AS estado_nombre,
        c.nombre AS cliente_nombre,
        c.apellido_paterno AS cliente_apellido,
        t.tipo
    FROM tramites t
    JOIN clientes c ON t.cliente_id = c.id
    JOIN estado_tramite et ON t.estado_id = et.id
";

if ($estado_id) {
    $query .= " WHERE et.id = :estado_id";
}

$stmt = $conn->prepare($query);

if ($estado_id) {
    $stmt->bindParam(':estado_id', $estado_id);
}

$stmt->execute();
$tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4 text-center">Seleccionar Estado de Trámite</h4>

                <!-- Formulario de selección -->
                <form method="GET" action="">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <select name="estado_id" class="form-select" required>
                                <option value="">-- Seleccionar Estado --</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= $estado['id'] ?>" <?= $estado['id'] == ($estado_id ?? '') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($estado['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-50 me-2">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="../pdf/ReporteIncidencias.php<?= $estado_id ? "?estado_id=$estado_id" : '' ?>" class="btn btn-outline-danger w-50">
                                <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Tabla de trámites -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($tramites) > 0): ?>
                                <?php foreach ($tramites as $tramite): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tramite['tramite_nombre']) ?></td>
                                        <td><?= htmlspecialchars($tramite['descripcion']) ?></td>
                                        <td><?= htmlspecialchars($tramite['fecha']) ?></td>
                                        <td><?= htmlspecialchars($tramite['cliente_nombre'] . ' ' . $tramite['cliente_apellido']) ?></td>
                                        <td><?= htmlspecialchars($tramite['estado_nombre']) ?></td>
                                        <td><?= htmlspecialchars($tramite['tipo']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No se encontraron trámites para el estado seleccionado.</td>
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
