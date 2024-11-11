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

// Obtener los parámetros del cliente y trámite
$tramite_id = $_GET['tramite_id'] ?? null;
$cliente_id = $_GET['cliente_id'] ?? null;
$order_by = $_GET['order_by'] ?? null; // Verificar si se solicita ordenación

if (!$tramite_id || !$cliente_id) {
    echo "Error: Trámite o Cliente no especificado.";
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Ajustar la consulta en función de la ordenación
$order_clause = $order_by === 'fecha_fin' ? 'ORDER BY dt.fecha_fin ASC' : '';
$stmt = $conn->prepare("
    SELECT c.nombre AS cliente_nombre, t.nombre AS tramite_nombre, p.nombre AS proceso_nombre, p.descripcion AS proceso_descripcion, 
           e.nombre AS empleado_nombre, ep.nombre AS estado_proceso, ep.id AS estado_id, dt.fecha_inicio, dt.fecha_fin, 
           p.id AS proceso_id, p.tipo AS tipo
    FROM detalle_tramite dt
    JOIN tramites t ON dt.tramite_id = t.id
    JOIN clientes c ON t.cliente_id = c.id
    JOIN procesos p ON dt.proceso_id = p.id
    JOIN empleados e ON dt.empleado_id = e.id
    JOIN estado_proceso ep ON dt.estado_id = ep.id
    WHERE t.id = :tramite_id AND c.id = :cliente_id
    $order_clause
");
$stmt->bindParam(':tramite_id', $tramite_id);
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-lg">
            <div class="card-body">
                <h2 class="text-primary text-center mt-4">
                    <i class="bi bi-list-check"></i> Lista de Procesos
                </h2>
                <?php if ($role === 'admin' || $role === 'operador'): ?>
                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mb-4">
                    <a href="tipoProceso.php?tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle"></i> Registrar Proceso
                    </a>
                    <a href="../pdf/generarPDFProcesos.php?tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Generar Reporte
                    </a>
                    <a href="?tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>&order_by=fecha_fin" class="btn btn-outline-primary">
                        <i class="bi bi-sort-down"></i> Ordenar por Fecha de Fin
                    </a>
                </div>
                <?php endif; ?>
                <!-- Tabla de Procesos -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Cliente</th>
                                <th>Trámite</th>
                                <th>Proceso</th>
                                <th>Descripción</th>
                                <th>Empleado</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Archivos Subidos</th>
                                <th>Acciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($procesos) > 0): ?>
                                <?php foreach ($procesos as $proceso): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($proceso['cliente_nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['tramite_nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['proceso_nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['proceso_descripcion']) ?></td>
                                        <td><?= htmlspecialchars($proceso['empleado_nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['fecha_inicio']) ?></td>
                                        <td><?= htmlspecialchars($proceso['fecha_fin']) ?></td>
                                        <td>
                                            <!-- Estado con colores dinámicos -->
                                            <span class="badge 
                                                <?php 
                                                    if ($proceso['estado_id'] == 1) echo 'bg-success'; // Terminado
                                                    elseif ($proceso['estado_id'] == 2) echo 'bg-warning'; // En Proceso
                                                    elseif ($proceso['estado_id'] == 3) echo 'bg-danger'; // Sin Procesar
                                                ?>">
                                                <?= htmlspecialchars($proceso['estado_proceso']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($proceso['tipo']) ?></td>
                                        <td>
                                            <?php
                                            $stmt_archivos = $conn->prepare("SELECT nombre FROM documentos WHERE tramite_id = :tramite_id AND proceso_id = :proceso_id");
                                            $stmt_archivos->bindParam(':tramite_id', $tramite_id);
                                            $stmt_archivos->bindParam(':proceso_id', $proceso['proceso_id']);
                                            $stmt_archivos->execute();
                                            $archivos = $stmt_archivos->fetchAll(PDO::FETCH_ASSOC);

                                            if (count($archivos) > 0) {
                                                foreach ($archivos as $archivo) {
                                                    echo htmlspecialchars($archivo['nombre']) . "<br>";
                                                }
                                            } else {
                                                echo "No se subieron archivos.";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($role === 'admin' || $role === 'operador' || $role === 'cliente'): ?>
                                                <a href="proceso<?= htmlspecialchars($proceso['tipo']) ?>Detalles.php?proceso_id=<?= $proceso['proceso_id'] ?>&tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>" class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Ver Detalles
                                                </a>
                                                <?php if ($role === 'admin' || $role === 'operador'): ?>
                                                    <a href="proceso<?= htmlspecialchars($proceso['tipo']) ?>Edicion.php?proceso_id=<?= $proceso['proceso_id'] ?>&tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>" class="btn btn-warning btn-sm">
                                                        <i class="bi bi-pencil"></i> Editar
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($role === 'admin'): ?>
                                                    <a href="procesoEliminacion.php?proceso_id=<?= $proceso['proceso_id'] ?>&tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este proceso?')">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <a href="../documentos/documento<?= htmlspecialchars($proceso['tipo']) ?>Registro.php?proceso_id=<?= $proceso['proceso_id'] ?>&tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>" class="btn btn-success btn-sm">
                                                <i class="bi bi-upload"></i> Subir Documentos
                                            </a>
                                        </td>
                                        <td>
    <form method="post" action="enviarNotificacion.php">
        <input type="hidden" name="proceso_id" value="<?= $proceso['proceso_id'] ?>">
        <input type="hidden" name="cliente_id" value="<?= $cliente_id ?>">
        <input type="hidden" name="tramite_id" value="<?= $tramite_id ?>">
        <button type="submit" class="btn btn-warning btn-sm">
            <i class="bi bi-envelope"></i> Enviar Notificación
        </button>
    </form>
</td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>

                                <tr>
                                    <td colspan="11" class="text-center">No se encontraron procesos relacionados.</td>
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
