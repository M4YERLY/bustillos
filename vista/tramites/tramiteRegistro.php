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

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener la lista de clientes para el select
$stmt_clientes = $conn->prepare("SELECT id, nombre, apellido_paterno, apellido_materno FROM clientes");
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de estados de la tabla estado_tramite
$stmt_estados = $conn->prepare("SELECT id, nombre FROM estado_tramite");
$stmt_estados->execute();
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario al enviar
if (isset($_POST['registrar'])) {
  $nombre = htmlspecialchars($_POST['nombre']);
  $descripcion = htmlspecialchars($_POST['descripcion']);
  $fecha = htmlspecialchars($_POST['fecha']);
  $cliente_id = htmlspecialchars($_POST['cliente_id']);
  $estado_id = htmlspecialchars($_POST['estado_id']); // Se selecciona del dropdown
  $tipo = htmlspecialchars($_POST['tipo']); // Capturar el tipo de trámite

  try {
    // Insertar el trámite
    $stmt = $conn->prepare("INSERT INTO tramites (nombre, descripcion, fecha, cliente_id, estado_id, tipo) VALUES (:nombre, :descripcion, :fecha, :cliente_id, :estado_id, :tipo)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':cliente_id', $cliente_id);
    $stmt->bindParam(':estado_id', $estado_id);
    $stmt->bindParam(':tipo', $tipo); // Guardar el tipo de trámite
    $stmt->execute();

    // Redirigir después de que todo el procesamiento termine
    header("Location: tramiteLista.php");
    exit();
  } catch (Exception $e) {
    echo "Error al registrar: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Trámite</title>
  <link rel="stylesheet" href="../../assets/vendor/css/core.css">
  <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
  <link rel="stylesheet" href="../../assets/css/demo.css">
</head>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <div class="layout-page">
        <div class="container ">
          <div class="authentication-wrapper authentication-basic container-p-y mx-auto col-md-6">
            <div class="authentication-inner">
              <div class="card">
                <div class="card-body">
                  <h4 class="mb-6">Registrar Nuevo Trámite</h4>

                  <form method="POST" action="">
                    <div class="mb-3">
                      <label for="nombre" class="form-label">Nombre del Trámite</label>
                      <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>

                    <div class="mb-3">
                      <label for="descripcion" class="form-label">Descripción</label>
                      <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                      <label for="fecha" class="form-label">Fecha de Creación</label>
                      <input type="date" class="form-control" id="fecha" name="fecha" required>
                    </div>

                    <div class="mb-3">
                      <label for="cliente_id" class="form-label">Cliente</label>
                      <select class="form-select" id="cliente_id" name="cliente_id" required>
                        <?php foreach ($clientes as $cliente): ?>
                          <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido_paterno'] . ' ' . $cliente['apellido_materno']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label for="estado_id" class="form-label">Estado del Trámite</label>
                      <select class="form-select" id="estado_id" name="estado_id" required>
                        <?php foreach ($estados as $estado): ?>
                          <option value="<?= $estado['id'] ?>" <?= ($estado['nombre'] == 'En proceso') ? 'selected' : '' ?>><?= $estado['nombre'] ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Campo para seleccionar el tipo de trámite -->
                    <div class="mb-3">
                      <label for="tipo" class="form-label">Tipo de Trámite</label>
                      <select class="form-select" id="tipo" name="tipo" required>
                        <option value="Importación">Importación</option>
                        <option value="Exportación">Exportación</option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <button type="submit" class="btn btn-primary" name="registrar">Registrar</button>
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