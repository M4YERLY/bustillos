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

$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener los detalles del proceso para editar
$stmt_proceso = $conn->prepare("
    SELECT p.*, dt.empleado_id, dt.fecha_inicio, dt.fecha_fin, dt.estado_id 
    FROM procesos p 
    JOIN detalle_tramite dt ON p.id = dt.proceso_id
    WHERE p.id = :proceso_id AND p.tramite_id = :tramite_id
");
$stmt_proceso->execute([':proceso_id' => $proceso_id, ':tramite_id' => $tramite_id]);
$proceso = $stmt_proceso->fetch(PDO::FETCH_ASSOC);

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
    $nombre = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $empleado_id = htmlspecialchars($_POST['empleado_id']);
    $fecha_inicio = htmlspecialchars($_POST['fecha_inicio']);
    $fecha_fin = htmlspecialchars($_POST['fecha_fin']);
    $estado_id = htmlspecialchars($_POST['estado_id']);
    $punto_origen = htmlspecialchars($_POST['punto_origen']);
    $punto_destino = htmlspecialchars($_POST['punto_destino']);
    $tipo_carga = htmlspecialchars($_POST['tipo_carga']);
    $aerolinea_principal = htmlspecialchars($_POST['aerolinea_principal']);  // Editar aerolínea principal

    try {
        // Actualizar el proceso
        $stmt = $conn->prepare("
            UPDATE procesos 
            SET nombre = :nombre, descripcion = :descripcion, punto_origen = :punto_origen, 
                punto_destino = :punto_destino, tipo_carga = :tipo_carga, aerolinea_principal = :aerolinea_principal 
            WHERE id = :proceso_id
        ");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':punto_origen', $punto_origen);
        $stmt->bindParam(':punto_destino', $punto_destino);
        $stmt->bindParam(':tipo_carga', $tipo_carga);
        $stmt->bindParam(':aerolinea_principal', $aerolinea_principal);  // Guardar aerolínea principal
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
        echo "Error al actualizar el proceso aéreo: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proceso Aéreo</title>
    <link rel="stylesheet" href="../../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="../../assets/css/demo.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 0.375rem;
        }

        .btn-custom {
            width: 100%;
            margin-top: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .row .col-md-6 {
            padding: 0 10px;
        }
    </style>
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
                                    <h4 class="mb-4 text-center">Editar Proceso Aéreo</h4>

                                    <form method="POST" action="">
                                        <div class="row">
                                            <!-- Nombre del proceso -->
                                            <div class="col-md-6 form-group">
                                                <label for="nombre" class="form-label">Nombre del Proceso</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($proceso['nombre']) ?>" required>
                                            </div>

                                            <!-- Descripción del proceso -->
                                            <div class="col-md-6 form-group">
                                                <label for="descripcion" class="form-label">Descripción</label>
                                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($proceso['descripcion']) ?></textarea>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Seleccionar empleado -->
                                            <div class="col-md-6 form-group">
                                                <label for="empleado_id" class="form-label">Seleccionar Empleado</label>
                                                <select class="form-select" id="empleado_id" name="empleado_id" required>
                                                    <?php foreach ($empleados as $empleado): ?>
                                                        <option value="<?= $empleado['id'] ?>" <?= ($empleado['id'] == $proceso['empleado_id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido_paterno']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Estado del proceso -->
                                            <div class="col-md-6 form-group">
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

                                        <div class="row">
                                            <!-- Fecha de inicio -->
                                            <div class="col-md-6 form-group">
                                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($proceso['fecha_inicio']) ?>" required>
                                            </div>

                                            <!-- Fecha de fin -->
                                            <div class="col-md-6 form-group">
                                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($proceso['fecha_fin']) ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Punto de origen -->
                                            <div class="col-md-6 form-group">
                                                <label for="punto_origen" class="form-label">Punto de Origen</label>
                                                <input type="text" class="form-control" id="punto_origen" name="punto_origen" value="<?= htmlspecialchars($proceso['punto_origen']) ?>" required>
                                            </div>

                                            <!-- Punto de destino -->
                                            <div class="col-md-6 form-group">
                                                <label for="punto_destino" class="form-label">Punto de Destino</label>
                                                <input type="text" class="form-control" id="punto_destino" name="punto_destino" value="<?= htmlspecialchars($proceso['punto_destino']) ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Tipo de carga -->
                                            <div class="col-md-6 form-group">
                                                <label for="tipo_carga" class="form-label">Tipo de Carga</label>
                                                <select class="form-select" id="tipo_carga" name="tipo_carga" required>
                                                    <option value="Frágil" <?= ($proceso['tipo_carga'] == 'Frágil') ? 'selected' : '' ?>>Frágil</option>
                                                    <option value="Perecedero" <?= ($proceso['tipo_carga'] == 'Perecedero') ? 'selected' : '' ?>>Perecedero</option>
                                                    <option value="Voluminoso" <?= ($proceso['tipo_carga'] == 'Voluminoso') ? 'selected' : '' ?>>Voluminoso</option>
                                                    <option value="General" <?= ($proceso['tipo_carga'] == 'General') ? 'selected' : '' ?>>General</option>
                                                </select>
                                            </div>

                                            <!-- Aerolínea principal -->
                                            <div class="col-md-6 form-group">
                                                <label for="aerolinea_principal" class="form-label">Aerolínea Principal</label>
                                                <input type="text" class="form-control" id="aerolinea_principal" name="aerolinea_principal" value="<?= htmlspecialchars($proceso['aerolinea_principal']) ?>" required>
                                            </div>
                                        </div>

                                        <!-- Botones -->
                                        <div class="d-flex justify-content-between">
                                            <button type="submit" class="btn btn-primary btn-custom" name="editar">Guardar Cambios</button>
                                            <a href="javascript:history.back()" class="btn btn-secondary btn-custom">Cancelar</a>
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

    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../assets/vendor/js/menu.js"></script>
    <script src="../../assets/vendor/js/helpers.js"></script>
    <script src="../../assets/js/config.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>