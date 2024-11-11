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

// Obtener el ID del trámite a editar
$tramite_id = $_GET['id'] ?? null;

if (!$tramite_id) {
    echo "Error: ID de trámite no especificado.";
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener los datos actuales del trámite
$stmt = $conn->prepare("SELECT * FROM tramites WHERE id = :id");
$stmt->bindParam(':id', $tramite_id);
$stmt->execute();
$tramite = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener la lista de clientes para el select
$stmt_clientes = $conn->prepare("SELECT id, nombre, apellido_paterno, apellido_materno FROM clientes");
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de estados de la tabla estado_tramite
$stmt_estados = $conn->prepare("SELECT id, nombre FROM estado_tramite");
$stmt_estados->execute();
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);

// Procesar la actualización al enviar el formulario
if (isset($_POST['actualizar'])) {
    $nombre = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $fecha = htmlspecialchars($_POST['fecha']);
    $cliente_id = htmlspecialchars($_POST['cliente_id']);
    $estado_id = htmlspecialchars($_POST['estado_id']);
    $tipo = htmlspecialchars($_POST['tipo']); // Capturar el tipo de trámite

    try {
        // Actualizar el trámite
        $stmt_actualizar = $conn->prepare("UPDATE tramites SET nombre = :nombre, descripcion = :descripcion, fecha = :fecha, cliente_id = :cliente_id, estado_id = :estado_id, tipo = :tipo WHERE id = :id");
        $stmt_actualizar->bindParam(':nombre', $nombre);
        $stmt_actualizar->bindParam(':descripcion', $descripcion);
        $stmt_actualizar->bindParam(':fecha', $fecha);
        $stmt_actualizar->bindParam(':cliente_id', $cliente_id);
        $stmt_actualizar->bindParam(':estado_id', $estado_id);
        $stmt_actualizar->bindParam(':tipo', $tipo); // Actualizar el tipo de trámite
        $stmt_actualizar->bindParam(':id', $tramite_id);
        $stmt_actualizar->execute();

        // Redirigir después de que todo el procesamiento termine
        header("Location: tramiteLista.php");
        exit();
    } catch (Exception $e) {
        echo "Error al actualizar el trámite: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Trámite</title>
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
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="mb-6">Editar Trámite</h4>

                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre del Trámite</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($tramite['nombre']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($tramite['descripcion']) ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="fecha" class="form-label">Fecha de Creación</label>
                                            <input type="date" class="form-control" id="fecha" name="fecha" value="<?= htmlspecialchars($tramite['fecha']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="cliente_id" class="form-label">Cliente</label>
                                            <select class="form-select" id="cliente_id" name="cliente_id" required>
                                                <?php foreach ($clientes as $cliente): ?>
                                                    <option value="<?= $cliente['id'] ?>" <?= ($tramite['cliente_id'] == $cliente['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido_paterno'] . ' ' . $cliente['apellido_materno']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="estado_id" class="form-label">Estado del Trámite</label>
                                            <select class="form-select" id="estado_id" name="estado_id" required>
                                                <?php foreach ($estados as $estado): ?>
                                                    <option value="<?= $estado['id'] ?>" <?= ($tramite['estado_id'] == $estado['id']) ? 'selected' : '' ?>>
                                                        <?= $estado['nombre'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Campo para seleccionar el tipo de trámite -->
                                        <div class="mb-3">
                                            <label for="tipo" class="form-label">Tipo de Trámite</label>
                                            <select class="form-select" id="tipo" name="tipo" required>
                                                <option value="Importación" <?= ($tramite['tipo'] == 'Importación') ? 'selected' : '' ?>>Importación</option>
                                                <option value="Exportación" <?= ($tramite['tipo'] == 'Exportación') ? 'selected' : '' ?>>Exportación</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary" name="actualizar">Actualizar</button>
                                            <a href="tramiteLista.php" class="btn btn-secondary">Cancelar</a>
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
</body>

</html>
