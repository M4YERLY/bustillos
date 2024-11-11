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

// Obtener los parámetros del cliente, trámite y proceso
$tramite_id = $_GET['tramite_id'] ?? null;
$proceso_id = $_GET['proceso_id'] ?? null;
$cliente_id = $_GET['cliente_id'] ?? null;

if (!$tramite_id || !$proceso_id || !$cliente_id) {
    echo "Error: Trámite, Proceso o Cliente no especificado.";
    exit();
}

// Obtener los detalles del cliente, trámite y proceso
$stmt = $conn->prepare("
    SELECT c.nombre AS cliente_nombre, c.apellido_paterno, c.apellido_materno, c.carnet_identidad, c.telefono, t.nombre AS tramite_nombre,
           t.descripcion AS tramite_descripcion, t.fecha AS tramite_fecha, p.nombre AS proceso_nombre, p.descripcion AS proceso_descripcion,
           p.punto_origen, p.punto_destino, p.peso_mercancia, p.tipo_carga, p.tipo, e.nombre AS estado_proceso, d.fecha_inicio, d.fecha_fin
    FROM clientes c
    JOIN tramites t ON c.id = t.cliente_id
    JOIN procesos p ON t.id = p.tramite_id
    JOIN detalle_tramite d ON p.id = d.proceso_id
    JOIN estado_proceso e ON d.estado_id = e.id
    WHERE c.id = :cliente_id AND t.id = :tramite_id AND p.id = :proceso_id
");
$stmt->execute([
    ':cliente_id' => $cliente_id,
    ':tramite_id' => $tramite_id,
    ':proceso_id' => $proceso_id
]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener los documentos subidos relacionados con el proceso
$stmt_docs = $conn->prepare("SELECT nombre, tipo_documento, version, archivo, fecha_subida FROM documentos WHERE tramite_id = :tramite_id AND proceso_id = :proceso_id");
$stmt_docs->execute([
    ':tramite_id' => $tramite_id,
    ':proceso_id' => $proceso_id
]);
$documentos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h4 class="text-center mb-4">Detalles del Proceso Terrestre</h4>

                            <!-- Información del Cliente -->
                            <div class="section-title">Información del Cliente</div>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Nombre Completo</th>
                                        <td><?= htmlspecialchars($info['cliente_nombre'] . ' ' . $info['apellido_paterno'] . ' ' . $info['apellido_materno']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Carnet de Identidad</th>
                                        <td><?= htmlspecialchars($info['carnet_identidad']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono</th>
                                        <td><?= htmlspecialchars($info['telefono']) ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Información del Trámite -->
                            <div class="section-title">Información del Trámite</div>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Nombre del Trámite</th>
                                        <td><?= htmlspecialchars($info['tramite_nombre']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Descripción</th>
                                        <td><?= htmlspecialchars($info['tramite_descripcion']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha del Trámite</th>
                                        <td><?= htmlspecialchars($info['tramite_fecha']) ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Información del Proceso -->
                            <div class="section-title">Información del Proceso</div>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Nombre del Proceso</th>
                                        <td><?= htmlspecialchars($info['proceso_nombre']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Descripción</th>
                                        <td><?= htmlspecialchars($info['proceso_descripcion']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Punto de Origen</th>
                                        <td><?= htmlspecialchars($info['punto_origen']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Punto de Destino</th>
                                        <td><?= htmlspecialchars($info['punto_destino']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Peso de la Mercancía</th>
                                        <td><?= htmlspecialchars($info['peso_mercancia']) ?> kg</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo de Carga</th>
                                        <td><?= htmlspecialchars($info['tipo_carga']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de Inicio</th>
                                        <td><?= htmlspecialchars($info['fecha_inicio']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de Fin</th>
                                        <td><?= htmlspecialchars($info['fecha_fin']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Estado del Proceso</th>
                                        <td><?= htmlspecialchars($info['estado_proceso']) ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Documentos Subidos -->
                            <div class="section-title">Documentos Subidos</div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Nombre del Documento</th>
                                            <th>Tipo de Documento</th>
                                            <th>Versión</th>
                                            <th>Fecha de Subida</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($documentos) > 0): ?>
                                            <?php foreach ($documentos as $documento): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($documento['nombre']) ?></td>
                                                    <td><?= htmlspecialchars($documento['tipo_documento']) ?></td>
                                                    <td><?= htmlspecialchars($documento['version']) ?></td>
                                                    <td><?= htmlspecialchars($documento['fecha_subida']) ?></td>
                                                    <td>
                                                        <a href="../../docs/procesos/<?= htmlspecialchars($documento['archivo']) ?>" target="_blank" class="btn btn-primary btn-sm">Ver Documento</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No se han subido documentos.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Botón de regreso -->
                            <a href="javascript:history.back()" class="btn btn-secondary btn-custom">Volver a la Lista de Procesos</a>

                        </div>
                    </div>
                </div>
            </div>

    <?php include("../../componentes/footer.php"); ?>