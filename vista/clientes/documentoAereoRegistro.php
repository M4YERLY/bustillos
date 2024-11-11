<?php
ob_start();
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

// Obtener los parámetros del trámite y proceso
$tramite_id = $_GET['tramite_id'] ?? null;
$proceso_id = $_GET['proceso_id'] ?? null;

if (!$tramite_id || !$proceso_id) {
    echo "Error: Trámite o Proceso no especificado.";
    exit();
}

// Obtener la información del trámite y proceso
$stmt = $conn->prepare("
    SELECT t.nombre AS tramite_nombre, p.nombre AS proceso_nombre
    FROM tramites t
    JOIN procesos p ON p.tramite_id = t.id
    WHERE t.id = :tramite_id AND p.id = :proceso_id
");
$stmt->execute([':tramite_id' => $tramite_id, ':proceso_id' => $proceso_id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

// Manejo del formulario al enviar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_documento = $_POST['nombre_documento'];
    $tipo_documento = $_POST['tipo_documento'];
    $version = $_POST['version'];
    $fecha_subida = date('Y-m-d'); // Fecha actual

    // Manejo de subida de múltiples archivos
    if (isset($_FILES['archivos'])) {
        $archivos = $_FILES['archivos'];
        $cantidad_archivos = count($archivos['name']);

        for ($i = 0; $i < $cantidad_archivos; $i++) {
            $nombre_archivo = basename($archivos['name'][$i]);
            $nombre_unico = $tramite_id . "_" . $i . "_" . time(); // Nombrado único
            $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
            $archivo_subido = "../../docs/procesos/" . $nombre_unico . "." . $extension;

            if (move_uploaded_file($archivos['tmp_name'][$i], $archivo_subido)) {
                // Insertamos el registro en la base de datos
                $stmt = $conn->prepare("INSERT INTO documentos (nombre, tramite_id, tipo_documento, fecha_subida, version, archivo, proceso_id) 
                                        VALUES (:nombre_documento, :tramite_id, :tipo_documento, :fecha_subida, :version, :archivo, :proceso_id)");

                // Enlazar parámetros
                $stmt->bindParam(':nombre_documento', $nombre_documento);
                $stmt->bindParam(':tramite_id', $tramite_id);
                $stmt->bindParam(':tipo_documento', $tipo_documento);
                $stmt->bindParam(':fecha_subida', $fecha_subida);
                $stmt->bindParam(':version', $version);
                $stmt->bindParam(':archivo', $archivo_subido);
                $stmt->bindParam(':proceso_id', $proceso_id);
                $stmt->execute();
            } else {
                echo "Error al subir el archivo $nombre_archivo.";
            }
        }
            // Redirigir a la lista de trámites después de subir los documentos
            header("Location: http://localhost/BUSTILLOS/vista/clientes/tramiteLista.php");
            exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-fixed">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Documentos</title>
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
                                    <h4 class="mb-4 text-center">Subir Documentos para Proceso</h4>

                                    <!-- Información del Trámite y Proceso -->
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Trámite:</strong></label>
                                        <span><?= htmlspecialchars($info['tramite_nombre']) ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Proceso:</strong></label>
                                        <span><?= htmlspecialchars($info['proceso_nombre']) ?></span>
                                    </div>

                                    <!-- Formulario de subida de documentos -->
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="nombre_documento" class="form-label">Nombre del Documento</label>
                                            <input type="text" name="nombre_documento" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                            <input type="text" name="tipo_documento" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="version" class="form-label">Versión del Documento</label>
                                            <input type="text" name="version" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="archivos" class="form-label">Subir Archivos</label>
                                            <input type="file" name="archivos[]" class="form-control" multiple required>
                                        </div>
                                        <input type="hidden" name="tramite_id" value="<?= htmlspecialchars($tramite_id) ?>">
                                        <input type="hidden" name="proceso_id" value="<?= htmlspecialchars($proceso_id) ?>">
                                        <button type="submit" class="btn btn-primary btn-custom">Subir Documentos</button>
                                        <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
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
