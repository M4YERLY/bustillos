<?php
session_start();
include("../../modelo/conexion.php");

// Verificar si el usuario ha iniciado sesión y si es cliente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener el ID del trámite de la URL
$tramite_id = $_GET['id_tramite'] ?? null;
if (!$tramite_id) {
    echo "Error: Trámite no especificado.";
    exit();
}

// Obtener los procesos asociados al trámite
$stmt_procesos = $conn->prepare("
    SELECT p.id, p.nombre, p.tipo, p.descripcion, p.estado_id, ep.nombre AS estado
    FROM procesos p
    JOIN estado_proceso ep ON p.estado_id = ep.id
    WHERE p.tramite_id = :tramite_id
");
$stmt_procesos->bindParam(':tramite_id', $tramite_id);
$stmt_procesos->execute();
$procesos = $stmt_procesos->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('../../componentes/headerCliente.php'); ?>

<div class="container-xxl">
    <div class="row justify-content-center mt-10">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-center">Lista de Procesos para el Trámite</h4>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Proceso</th>
                                    <th>Descripción</th>
                    
                                    <th>Estado</th>
                                    <th>Tipo</th>
                                    <th>Archivos Subidos</th> <!-- Nueva columna para los archivos subidos -->
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($procesos as $index => $proceso): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($proceso['nombre']) ?></td>
                                        <td><?= htmlspecialchars($proceso['descripcion']) ?></td>
                    
                                        <td><?= htmlspecialchars($proceso['estado']) ?></td>
                                        <td><?= htmlspecialchars($proceso['tipo']) ?></td>

                                        <?php
                                        // Obtener archivos subidos para este proceso
                                        $stmt_documentos = $conn->prepare("
                                            SELECT nombre, archivo FROM documentos 
                                            WHERE proceso_id = :proceso_id
                                        ");
                                        $stmt_documentos->bindParam(':proceso_id', $proceso['id']);
                                        $stmt_documentos->execute();
                                        $documentos = $stmt_documentos->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <td>
                                            <?php if (!empty($documentos)): ?>
                                                <ul>
                                                    <?php foreach ($documentos as $doc): ?>
                                                        <li>
                                                            <a href="<?= htmlspecialchars($doc['archivo']) ?>" target="_blank"><?= htmlspecialchars($doc['nombre']) ?></a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                No hay documentos subidos.
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php
                                            // Determinar la URL de redirección según el tipo de proceso
                                            $tipo = strtolower(trim($proceso['tipo'])); // Convertir a minúsculas y eliminar espacios
                                            $proceso_id = $proceso['id'];
                                            switch ($tipo) {
                                                case 'aéreo':
                                                case 'aereo': // Manejar ambas opciones con y sin tilde
                                                    $url = "../clientes/documentoAereoRegistro.php?proceso_id={$proceso_id}&tramite_id={$tramite_id}";
                                                    break;
                                                case 'terrestre':
                                                    $url = "../clientes/documentoTerrestreRegistro.php?proceso_id={$proceso_id}&tramite_id={$tramite_id}";
                                                    break;
                                                case 'naval':
                                                    $url = "../clientes/documentoNavalRegistro.php?proceso_id={$proceso_id}&tramite_id={$tramite_id}";
                                                    break;
                                                default:
                                                    error_log("Tipo no reconocido: $tipo");
                                                    $url = "#"; // En caso de que no sea un tipo válido
                                                    break;
                                            }
                                            ?>
                                            <a href="<?= $url ?>" class="btn btn-primary btn-sm">
                                                Subir Documentos
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($procesos)): ?>
                        <p class="text-center mt-3">No se encontraron procesos asociados a este trámite.</p>
                    <?php endif; ?>
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
