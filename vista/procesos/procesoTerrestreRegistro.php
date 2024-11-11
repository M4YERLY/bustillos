<?php
ob_start();
session_start();
include("../../modelo/conexion.php");
include("../../componentes/header.php"); // Incluir el header

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener los datos del trámite y del cliente usando el ID del trámite
$tramite_id = $_GET['tramite_id'] ?? null;
$cliente_id = $_GET['cliente_id'] ?? null;

// Consulta para obtener los detalles del trámite
$stmt_tramite = $conn->prepare("SELECT t.nombre, t.tipo FROM tramites t WHERE t.id = :tramite_id");
$stmt_tramite->bindParam(':tramite_id', $tramite_id);
$stmt_tramite->execute();
$tramite = $stmt_tramite->fetch(PDO::FETCH_ASSOC);

// Obtener la lista de empleados
$stmt_empleados = $conn->prepare("SELECT id, nombre, apellido_paterno FROM empleados");
$stmt_empleados->execute();
$empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de estados de proceso
$stmt_estados = $conn->prepare("SELECT id, nombre FROM estado_proceso");
$stmt_estados->execute();
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario al enviar
if (isset($_POST['registrar'])) {
    $nombre_proceso = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $empleado_id = htmlspecialchars($_POST['empleado_id']);
    $fecha_inicio = htmlspecialchars($_POST['fecha_inicio']);
    $fecha_fin = htmlspecialchars($_POST['fecha_fin']);
    $estado_id = htmlspecialchars($_POST['estado_id']);
    $tipo = 'Terrestre';  // Establecer el tipo de proceso como "Terrestre"
    $punto_origen = htmlspecialchars($_POST['punto_origen']);
    $punto_destino = htmlspecialchars($_POST['punto_destino']);
    $peso_mercancia = htmlspecialchars($_POST['peso_mercancia']);
    $tipo_carga = htmlspecialchars($_POST['tipo_carga']);

    // Insertar en la tabla de procesos
    try {
        $stmt = $conn->prepare("
            INSERT INTO procesos (nombre, descripcion, tramite_id, estado_id, tipo, punto_origen, punto_destino, peso_mercancia, tipo_carga)
            VALUES (:nombre, :descripcion, :tramite_id, :estado_id, :tipo, :punto_origen, :punto_destino, :peso_mercancia, :tipo_carga)
        ");
        $stmt->bindParam(':nombre', $nombre_proceso);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':tramite_id', $tramite_id);
        $stmt->bindParam(':estado_id', $estado_id);
        $stmt->bindParam(':tipo', $tipo);  // Insertar el tipo "Terrestre"
        $stmt->bindParam(':punto_origen', $punto_origen);
        $stmt->bindParam(':punto_destino', $punto_destino);
        $stmt->bindParam(':peso_mercancia', $peso_mercancia);
        $stmt->bindParam(':tipo_carga', $tipo_carga);
        $stmt->execute();

        $proceso_id = $conn->lastInsertId();

        // Insertar en la tabla detalle_tramite
        $stmt_detalle = $conn->prepare("
            INSERT INTO detalle_tramite (tramite_id, proceso_id, empleado_id, fecha_inicio, fecha_fin, estado_id)
            VALUES (:tramite_id, :proceso_id, :empleado_id, :fecha_inicio, :fecha_fin, :estado_id)
        ");
        $stmt_detalle->bindParam(':tramite_id', $tramite_id);
        $stmt_detalle->bindParam(':proceso_id', $proceso_id);
        $stmt_detalle->bindParam(':empleado_id', $empleado_id);
        $stmt_detalle->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt_detalle->bindParam(':fecha_fin', $fecha_fin);
        $stmt_detalle->bindParam(':estado_id', $estado_id);
        $stmt_detalle->execute();

        // Redirigir después de que todo el procesamiento termine
        header("Location: procesoLista.php?tramite_id=$tramite_id&cliente_id=$cliente_id");
        exit();
    } catch (Exception $e) {
        echo "Error al registrar el proceso: " . $e->getMessage();
    }
}
?>
<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-lg">
            <div class="card-body">
                <h4 class="text-center text-primary">Registrar Proceso Terrestre</h4>
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre del Proceso</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>
                    </div>
                    <!-- Empleado y Estado -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="empleado_id" class="form-label">Seleccionar Empleado</label>
                            <select class="form-select" id="empleado_id" name="empleado_id" required>
                                <?php foreach ($empleados as $empleado): ?>
                                    <option value="<?= $empleado['id'] ?>"><?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido_paterno']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="estado_id" class="form-label">Estado del Proceso</label>
                            <select class="form-select" id="estado_id" name="estado_id" required>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= $estado['id'] ?>"><?= htmlspecialchars($estado['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Fechas -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                        </div>
                    </div>
                    <!-- Detalles del Proceso -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="punto_origen" class="form-label">Punto de Origen</label>
                            <input type="text" class="form-control" id="punto_origen" name="punto_origen" required>
                        </div>
                        <div class="col-md-6">
                            <label for="punto_destino" class="form-label">Punto de Destino</label>
                            <input type="text" class="form-control" id="punto_destino" name="punto_destino" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="peso_mercancia" class="form-label">Peso de la Mercancía (kg)</label>
                            <input type="number" class="form-control" id="peso_mercancia" name="peso_mercancia" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_carga" class="form-label">Tipo de Carga</label>
                            <select class="form-select" id="tipo_carga" name="tipo_carga" required>
                                <option value="Frágil">Frágil</option>
                                <option value="Perecedero">Perecedero</option>
                                <option value="Voluminoso">Voluminoso</option>
                                <option value="General">General</option>
                            </select>
                        </div>
                    </div>
                    <!-- Botones -->
                    <div class="text-end">
                        <button type="submit" name="registrar" class="btn btn-primary">Registrar</button>
                        <a href="procesoLista.php?tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include("../../componentes/footer.php"); ?>