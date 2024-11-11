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

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Funcionalidad de búsqueda
$busqueda = $_GET['busqueda'] ?? '';

// Consulta para obtener la lista de trámites, filtrando si hay una búsqueda
$query = "
    SELECT t.id, t.nombre, t.descripcion, t.fecha, et.nombre AS estado_nombre, c.nombre AS cliente_nombre, c.apellido_paterno AS cliente_apellido, t.tipo, t.cliente_id
    FROM tramites t 
    JOIN clientes c ON t.cliente_id = c.id
    JOIN estado_tramite et ON t.estado_id = et.id
";

if (!empty($busqueda)) {
    $query .= " WHERE t.nombre LIKE :busqueda OR c.nombre LIKE :busqueda OR c.apellido_paterno LIKE :busqueda";
}

$stmt = $conn->prepare($query);

// Bind del parámetro de búsqueda
if (!empty($busqueda)) {
    $busqueda_param = '%' . $busqueda . '%';
    $stmt->bindParam(':busqueda', $busqueda_param);
}

$stmt->execute();
$tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-lg">
            <div class="card-body">
                <h2 class="text-primary text-center mt-4">
                    <i class="bi bi-list-check"></i> Lista de Trámites
                </h2>

                <!-- Barra de búsqueda y botones de acción -->
                <div class="d-flex justify-content-between mb-4">
                    <!-- Formulario de búsqueda alineado a la izquierda -->
                    <form method="GET" action="" class="d-flex">
                        <input type="text" name="busqueda" class="form-control me-2" placeholder="Buscar trámites..." value="<?= htmlspecialchars($busqueda) ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </form>

                    <!-- Botones de acción alineados a la derecha -->
                    <div>
                        <a href="tramiteRegistro.php" class="btn btn-outline-success me-2">
                            <i class="bi bi-plus-circle"></i> Agregar Trámite
                        </a>
                        <a href="../pdf/tramiteLista.php" class="btn btn-outline-danger">
                            <i class="bi bi-file-earmark-pdf"></i> Generar Reporte
                        </a>
                    </div>
                </div>

                <!-- Tabla de Trámites -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($tramites) > 0): ?>
                                <?php foreach ($tramites as $tramite): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tramite['nombre']) ?></td>
                                        <td><?= htmlspecialchars($tramite['descripcion']) ?></td>
                                        <td><?= htmlspecialchars($tramite['fecha']) ?></td>
                                        <td><?= htmlspecialchars($tramite['cliente_nombre'] . ' ' . $tramite['cliente_apellido']) ?></td>
                                        <td><?= htmlspecialchars($tramite['estado_nombre']) ?></td>
                                        <td><?= htmlspecialchars($tramite['tipo']) ?></td>
                                        <td>
                                            <?php if ($role === 'admin' || $role === 'operador'): ?>
                                                <!-- Botones de acción -->
                                                <a href="tramiteEdicion.php?id=<?= $tramite['id'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil"></i> Editar
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($role === 'admin'): ?>
                                                <a href="tramiteEliminacion.php?id=<?= $tramite['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este trámite?')">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </a>
                                            <?php endif; ?>
                                            <a href="../procesos/procesoLista.php?tramite_id=<?= $tramite['id'] ?>&cliente_id=<?= $tramite['cliente_id'] ?>" class="btn btn-info btn-sm">
                                                <i class="bi bi-eye"></i> Ver Procesos
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron trámites.</td>
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