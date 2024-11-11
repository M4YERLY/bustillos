<?php
ob_start();
session_start();
include("../../modelo/conexion.php"); // Incluir la conexión a la base de datos
include("../../componentes/header.php"); // Incluir el header

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener los parámetros del trámite, cliente y proceso
$tramite_id = $_GET['tramite_id'] ?? null;
$cliente_id = $_GET['cliente_id'] ?? null;
$proceso_id = $_GET['proceso_id'] ?? null;

if (!$tramite_id || !$cliente_id || !$proceso_id) {
    echo "Error: Trámite, Cliente o Proceso no especificado.";
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener los detalles del proceso
$stmt_proceso = $conn->prepare("
    SELECT p.*, dt.empleado_id, dt.fecha_inicio, dt.fecha_fin, dt.estado_id 
    FROM procesos p 
    JOIN detalle_tramite dt ON p.id = dt.proceso_id
    WHERE p.id = :proceso_id AND p.tramite_id = :tramite_id
");
$stmt_proceso->execute([':proceso_id' => $proceso_id, ':tramite_id' => $tramite_id]);
$proceso = $stmt_proceso->fetch(PDO::FETCH_ASSOC);

if (!$proceso) {
    echo "Error: Proceso no encontrado.";
    exit();
}

// Obtener la lista de empleados
$stmt_empleados = $conn->prepare("SELECT id, nombre, apellido_paterno FROM empleados");
$stmt_empleados->execute();
$empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de estados de proceso
$stmt_estados = $conn->prepare("SELECT id, nombre FROM estado_proceso");
$stmt_estados->execute();
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario al enviar
if (isset($_POST['editar'])) {
    $nombre_proceso = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $empleado_id = htmlspecialchars($_POST['empleado_id']);
    $fecha_inicio = htmlspecialchars($_POST['fecha_inicio']);
    $fecha_fin = htmlspecialchars($_POST['fecha_fin']);
    $estado_id = htmlspecialchars($_POST['estado_id']);
    $punto_origen = htmlspecialchars($_POST['punto_origen']);
    $punto_destino = htmlspecialchars($_POST['punto_destino']);
    $peso_mercancia = htmlspecialchars($_POST['peso_mercancia']);
    $tipo_carga = htmlspecialchars($_POST['tipo_carga']);

    try {
        // Actualizar el proceso
        $stmt = $conn->prepare("
            UPDATE procesos 
            SET nombre = :nombre, descripcion = :descripcion, punto_origen = :punto_origen, punto_destino = :punto_destino, 
                peso_mercancia = :peso_mercancia, tipo_carga = :tipo_carga 
            WHERE id = :proceso_id
        ");
        $stmt->bindParam(':nombre', $nombre_proceso);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':punto_origen', $punto_origen);
        $stmt->bindParam(':punto_destino', $punto_destino);
        $stmt->bindParam(':peso_mercancia', $peso_mercancia);
        $stmt->bindParam(':tipo_carga', $tipo_carga);
        $stmt->bindParam(':proceso_id', $proceso_id);
        $stmt->execute();

        // Actualizar el detalle del trámite
        $stmt_detalle = $conn->prepare("
            UPDATE detalle_tramite 
            SET empleado_id = :empleado_id, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, estado_id = :estado_id
            WHERE proceso_id = :proceso_id
        ");
        $stmt_detalle->bindParam(':empleado_id', $empleado_id);
        $stmt_detalle->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt_detalle->bindParam(':fecha_fin', $fecha_fin);
        $stmt_detalle->bindParam(':estado_id', $estado_id);
        $stmt_detalle->bindParam(':proceso_id', $proceso_id);
        $stmt_detalle->execute();

        // Redirigir después de la edición exitosa
        header("Location: procesoLista.php?tramite_id=$tramite_id&cliente_id=$cliente_id");
        exit();
    } catch (Exception $e) {
        echo "Error al actualizar el proceso: " . $e->getMessage();
    }
}
?>

<!-- Formulario para editar proceso terrestre -->
<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proceso Terrestre</title>
    <link rel="stylesheet" href="../../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="../../assets/css/demo.css">
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <div class="layout-page">
                <div class="container-xxl">
                    <div class="authentication-wrapper authentication-basic container-p-y mx-auto col-md-6">
                        <div class="authentication-inner">
                            <div class="card shadow-lg">
                                <div class="card-body">
                                    <h4 class="mb-4 text-center">Editar Proceso Terrestre</h4>
                                    <form method="POST" action="">
                                        <!-- Nombre y Descripción -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="nombre" class="form-label">Nombre del Proceso</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($proceso['nombre']) ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="descripcion" class="form-label">Descripción</label>
                                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($proceso['descripcion']) ?></textarea>
                                            </div>
                                        </div>
                                        <!-- Selección de empleado y estado -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="empleado_id" class="form-label">Seleccionar Empleado</label>
                                                <select class="form-select" id="empleado_id" name="empleado_id" required>
                                                    <?php foreach ($empleados as $empleado): ?>
                                                        <option value="<?= $empleado['id'] ?>" <?= ($empleado['id'] == $proceso['empleado_id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido_paterno']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="estado_id" class="form-label">Estado del Proceso</label>
                                                <select class="form-select" id="estado_id" name="estado_id" required>
                                                    <?php foreach ($estados as $estado): ?>
                                                        <option value="<?= $estado['id'] ?>" <?= ($estado['id'] == $proceso['estado_id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($estado['nombre']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Fechas -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($proceso['fecha_inicio']) ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($proceso['fecha_fin']) ?>" required>
                                            </div>
                                        </div>
                                        <!-- Detalles adicionales -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="punto_origen" class="form-label">Punto de Origen</label>
                                                <input type="text" class="form-control" id="punto_origen" name="punto_origen" value="<?= htmlspecialchars($proceso['punto_origen']) ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="punto_destino" class="form-label">Punto de Destino</label>
                                                <input type="text" class="form-control" id="punto_destino" name="punto_destino" value="<?= htmlspecialchars($proceso['punto_destino']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="peso_mercancia" class="form-label">Peso de la Mercancía (kg)</label>
                                                <input type="number" class="form-control" id="peso_mercancia" name="peso_mercancia" value="<?= htmlspecialchars($proceso['peso_mercancia']) ?>" step="0.01" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="tipo_carga" class="form-label">Tipo de Carga</label>
                                                <select class="form-select" id="tipo_carga" name="tipo_carga" required>
                                                    <option value="Frágil" <?= ($proceso['tipo_carga'] == 'Frágil') ? 'selected' : '' ?>>Frágil</option>
                                                    <option value="Perecedero" <?= ($proceso['tipo_carga'] == 'Perecedero') ? 'selected' : '' ?>>Perecedero</option>
                                                    <option value="Voluminoso" <?= ($proceso['tipo_carga'] == 'Voluminoso') ? 'selected' : '' ?>>Voluminoso</option>
                                                    <option value="General" <?= ($proceso['tipo_carga'] == 'General') ? 'selected' : '' ?>>General</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Botones -->
                                        <div class="text-end">
                                            <button type="submit" name="editar" class="btn btn-primary">Guardar Cambios</button>
                                            <a href="procesoLista.php?tramite_id=<?= $tramite_id ?>&cliente_id=<?= $cliente_id ?>" class="btn btn-secondary">Cancelar</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
