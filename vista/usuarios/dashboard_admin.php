<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Incluir la conexión a la base de datos
include("../../modelo/conexion.php");
include("../../componentes/header.php"); // Incluir el header
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener el número de clientes, administradores y empleados
$usuarios = [
    'total_clientes' => 0,
    'total_admins' => 0,
    'total_empleados' => 0
];
$queryUsuarios = "
    SELECT 
        (SELECT COUNT(*) FROM usuarios WHERE role = 'cliente') AS total_clientes, 
        (SELECT COUNT(*) FROM usuarios WHERE role = 'admin') AS total_admins, 
        (SELECT COUNT(*) FROM usuarios WHERE role = 'empleado') AS total_empleados";
$stmtUsuarios = $conn->prepare($queryUsuarios);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetch(PDO::FETCH_ASSOC);

// Obtener el número total de trámites, procesos y documentos
$tramites = [
    'total_tramites' => 0
];
$queryTramites = "SELECT COUNT(*) AS total_tramites FROM tramites";
$stmtTramites = $conn->prepare($queryTramites);
$stmtTramites->execute();
$tramites = $stmtTramites->fetch(PDO::FETCH_ASSOC);

$procesos = [
    'total_procesos' => 0,
    'total_naval' => 0,
    'total_terrestre' => 0,
    'total_aereo' => 0,
    'total_terminado' => 0,
    'total_en_proceso' => 0,
    'total_sin_procesar' => 0
];
$queryProcesos = "
    SELECT COUNT(*) AS total_procesos,
        SUM(CASE WHEN tipo = 'Naval' THEN 1 ELSE 0 END) AS total_naval,
        SUM(CASE WHEN tipo = 'Terrestre' THEN 1 ELSE 0 END) AS total_terrestre,
        SUM(CASE WHEN tipo = 'Aéreo' THEN 1 ELSE 0 END) AS total_aereo
    FROM procesos";
$stmtProcesos = $conn->prepare($queryProcesos);
$stmtProcesos->execute();
$procesos = array_merge($procesos, $stmtProcesos->fetch(PDO::FETCH_ASSOC));

// Obtener los estados de los procesos
$queryEstadoProcesos = "
    SELECT 
        SUM(CASE WHEN estado_id = 1 THEN 1 ELSE 0 END) AS total_terminado,
        SUM(CASE WHEN estado_id = 2 THEN 1 ELSE 0 END) AS total_en_proceso,
        SUM(CASE WHEN estado_id = 3 THEN 1 ELSE 0 END) AS total_sin_procesar
    FROM procesos";
$stmtEstadoProcesos = $conn->prepare($queryEstadoProcesos);
$stmtEstadoProcesos->execute();
$procesos = array_merge($procesos, $stmtEstadoProcesos->fetch(PDO::FETCH_ASSOC));

// Obtener el número de documentos recibidos
$documentos = [
    'total_documentos' => 0
];
$queryDocumentos = "SELECT COUNT(*) AS total_documentos FROM documentos";
$stmtDocumentos = $conn->prepare($queryDocumentos);
$stmtDocumentos->execute();
$documentos = $stmtDocumentos->fetch(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../assets/vendor/css/core.css">
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="../../assets/css/demo.css">
    <style>
        .dashboard-wrapper {
            margin-top: 20px;
            padding: 20px;
        }

        .dashboard-card {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .dashboard-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .chart-container {
            width: 100%;
            height: 300px;
        }

        .row {
            display: flex;
            justify-content: space-around;
        }

        .col-md-6 {
            flex: 0 0 45%;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="dashboard-wrapper container">
            <h1 class="mb-4 text-center">Dashboard Admin</h1>

            <div class="row">
                <!-- Gráfico de Usuarios -->
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="dashboard-title">Número de Usuarios</div>
                        <div class="chart-container">
                            <canvas id="usuariosChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Trámites, Procesos y Documentos -->
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="dashboard-title">Trámites, Procesos y Documentos Recibidos</div>
                        <div class="chart-container">
                            <canvas id="tramitesProcesosDocumentosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Gráfico de Procesos por Tipo -->
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="dashboard-title">Procesos por Tipo</div>
                        <div class="chart-container">
                            <canvas id="procesosChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Estados de Procesos -->
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="dashboard-title">Estados de los Procesos</div>
                        <div class="chart-container">
                            <canvas id="estadoProcesosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Gráfico de Usuarios (Clientes, Administradores, Empleados)
        const usuariosCtx = document.getElementById('usuariosChart').getContext('2d');
        new Chart(usuariosCtx, {
            type: 'bar',
            data: {
                labels: ['Clientes', 'Administradores', 'Empleados'],
                datasets: [{
                    label: 'Cantidad',
                    data: [<?= $usuarios['total_clientes'] ?>, <?= $usuarios['total_admins'] ?>, <?= $usuarios['total_empleados'] ?>],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico de Trámites, Procesos y Documentos
        const tramitesProcesosDocumentosCtx = document.getElementById('tramitesProcesosDocumentosChart').getContext('2d');
        new Chart(tramitesProcesosDocumentosCtx, {
            type: 'bar',
            data: {
                labels: ['Trámites', 'Procesos', 'Documentos Recibidos'],
                datasets: [{
                    label: 'Cantidad',
                    data: [<?= $tramites['total_tramites'] ?>, <?= $procesos['total_procesos'] ?>, <?= $documentos['total_documentos'] ?>],
                    backgroundColor: ['#4caf50', '#36b9cc', '#ff9f40']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico de Procesos por Tipo (Naval, Terrestre, Aéreo)
        const procesosCtx = document.getElementById('procesosChart').getContext('2d');
        new Chart(procesosCtx, {
            type: 'doughnut',
            data: {
                labels: ['Naval', 'Terrestre', 'Aéreo'],
                datasets: [{
                    data: [<?= $procesos['total_naval'] ?>, <?= $procesos['total_terrestre'] ?>, <?= $procesos['total_aereo'] ?>],
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56']
                }]
            },
            options: {
                responsive: true
            }
        });

        // Gráfico de Estados de los Procesos (Terminado, En proceso, Sin procesar)
        const estadoProcesosCtx = document.getElementById('estadoProcesosChart').getContext('2d');
        new Chart(estadoProcesosCtx, {
            type: 'pie',
            data: {
                labels: ['Terminado', 'En proceso', 'Sin procesar'],
                datasets: [{
                    data: [<?= $procesos['total_terminado'] ?>, <?= $procesos['total_en_proceso'] ?>, <?= $procesos['total_sin_procesar'] ?>],
                    backgroundColor: ['#4caf50', '#36b9cc', '#ff6384']
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>

    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../assets/vendor/js/menu.js"></script>
    <script src="../../assets/vendor/js/helpers.js"></script>
    <script src="../../assets/js/config.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>

</html>