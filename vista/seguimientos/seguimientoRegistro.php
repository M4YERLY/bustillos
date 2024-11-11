<?php
ob_start();
session_start();
include("../../modelo/conexion.php");
include("../../componentes/header.php");

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener la lista de trámites
$stmt_tramites = $conn->prepare("SELECT id, nombre FROM tramites");
$stmt_tramites->execute();
$tramites = $stmt_tramites->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de empleados
$stmt_empleados = $conn->prepare("SELECT id, nombre FROM empleados");
$stmt_empleados->execute();
$empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario al enviar
if (isset($_POST['registrar'])) {
    $tramite_id = htmlspecialchars($_POST['tramite_id']);
    $observaciones = htmlspecialchars($_POST['observaciones']);
    $fecha_seguimiento = htmlspecialchars($_POST['fecha_seguimiento']);
    $empleado_id = htmlspecialchars($_POST['empleado_id']);
    $tipo_seguimiento = htmlspecialchars($_POST['tipo_seguimiento']);

    try {
        $stmt = $conn->prepare("
            INSERT INTO seguimientos (tramite_id, observaciones, fecha_seguimiento, empleado_id, tipo_seguimiento) 
            VALUES (:tramite_id, :observaciones, :fecha_seguimiento, :empleado_id, :tipo_seguimiento)
        ");
        $stmt->bindParam(':tramite_id', $tramite_id);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':fecha_seguimiento', $fecha_seguimiento);
        $stmt->bindParam(':empleado_id', $empleado_id);
        $stmt->bindParam(':tipo_seguimiento', $tipo_seguimiento);
        $stmt->execute();

        header("Location: seguimientoLista.php");
        exit();
    } catch (Exception $e) {
        echo "Error al registrar el seguimiento: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Seguimiento</title>
    <link rel="stylesheet" href="../../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="../../assets/css/demo.css">
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <div class="layout-page">
                <div class="container-xxl">
                    <div class="card shadow-lg mx-auto col-md-6">
                        <div class="card-body">
                            <h4 class="mb-4 text-center">Registrar Seguimiento</h4>
                            <form method="POST" action="">
                                <!-- Selección de trámite -->
                                <div class="mb-3">
                                    <label for="tramite_id" class="form-label">Trámite</label>
                                    <select class="form-select" id="tramite_id" name="tramite_id" required>
                                        <?php foreach ($tramites as $tramite): ?>
                                            <option value="<?= $tramite['id'] ?>"><?= htmlspecialchars($tramite['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Observaciones -->
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3" required></textarea>
                                </div>

                                <!-- Fecha de seguimiento -->
                                <div class="mb-3">
                                    <label for="fecha_seguimiento" class="form-label">Fecha de Seguimiento</label>
                                    <input type="date" class="form-control" id="fecha_seguimiento" name="fecha_seguimiento" required>
                                </div>

                                <!-- Selección de empleado -->
                                <div class="mb-3">
                                    <label for="empleado_id" class="form-label">Empleado</label>
                                    <select class="form-select" id="empleado_id" name="empleado_id" required>
                                        <?php foreach ($empleados as $empleado): ?>
                                            <option value="<?= $empleado['id'] ?>"><?= htmlspecialchars($empleado['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tipo de seguimiento -->
                                <div class="mb-3">
                                    <label for="tipo_seguimiento" class="form-label">Tipo de Seguimiento</label>
                                    <select class="form-select" id="tipo_seguimiento" name="tipo_seguimiento" required>
                                        <option value="Aprobado">Aprobado</option>
                                        <option value="En proceso">En proceso</option>
                                        <option value="Sin aprobar">Sin aprobar</option>
                                    </select>
                                </div>

                                <!-- Botón de enviar -->
                                <button type="submit" class="btn btn-primary" name="registrar">Registrar Seguimiento</button>
                            </form>
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
</body>

</html>