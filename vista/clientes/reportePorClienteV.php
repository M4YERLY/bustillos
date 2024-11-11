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

// Obtener la lista de clientes
$stmt_clientes = $conn->prepare("SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo FROM clientes");
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener los procesos según el cliente seleccionado
$cliente_id = $_GET['cliente_id'] ?? null;

if ($cliente_id) {
    // Validar cliente_id como entero
    if (!filter_var($cliente_id, FILTER_VALIDATE_INT)) {
        die("ID de cliente inválido.");
    }

    // Consulta de procesos
    $stmt_procesos = $conn->prepare("
        SELECT 
            cl.id AS cliente_id,
            CONCAT(cl.nombre, ' ', cl.apellido_paterno, ' ', cl.apellido_materno) AS cliente_nombre_completo,
            tr.nombre AS tramite_nombre,
            p.nombre AS proceso_nombre,
            p.descripcion AS proceso_descripcion,
            p.punto_origen,
            p.punto_destino,
            p.tipo_carga,
            p.peso_mercancia,
            ep.nombre AS estado_proceso,
            dt.fecha_inicio,
            dt.fecha_fin
        FROM clientes cl
        JOIN tramites tr ON cl.id = tr.cliente_id
        JOIN procesos p ON tr.id = p.tramite_id
        JOIN detalle_tramite dt ON p.id = dt.proceso_id
        JOIN estado_proceso ep ON dt.estado_id = ep.id
        WHERE cl.id = :cliente_id
        ORDER BY dt.fecha_inicio DESC
    ");
    $stmt_procesos->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $stmt_procesos->execute();
    $procesos = $stmt_procesos->fetchAll(PDO::FETCH_ASSOC);
} else {
    $procesos = [];
}
?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4 text-center">Reporte de Procesos por Cliente</h4>

                <!-- Formulario de selección -->
                <form method="GET" action="">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <select name="cliente_id" class="form-select" required>
                                <option value="">-- Seleccionar Cliente --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>" <?= $cliente['id'] == ($cliente_id ?? '') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cliente['nombre_completo'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-50 me-2">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="../pdf/reportePorCliente.php<?= isset($cliente_id) ? "?cliente_id=" . urlencode($cliente_id) : '' ?>" class="btn btn-outline-danger w-50">
                                <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Tabla de procesos -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Trámite</th>
                                <th>Proceso</th>
                                <th>Descripción</th>
                                <th>Punto de Origen</th>
                                <th>Punto de Destino</th>
                                <th>Tipo de Carga</th>
                                <th>Peso de Mercancía (kg)</th>
                                <th>Estado</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($procesos) > 0): ?>
                                <?php foreach ($procesos as $proceso): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($proceso['tramite_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['proceso_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['proceso_descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['punto_origen'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['punto_destino'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['tipo_carga'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['peso_mercancia'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['estado_proceso'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proceso['fecha_fin'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <?= $cliente_id ? "No se encontraron procesos para el cliente seleccionado." : "Seleccione un cliente para ver los procesos." ?>
                                    </td>
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
