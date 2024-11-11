<?php
// Verificar si la sesión ya está activa antes de iniciarla
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Conectar a la base de datos
include_once("../../modelo/conexion.php");

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener el rol del usuario actual
$stmt = $conn->prepare("SELECT role FROM usuarios WHERE username = :usuario");
$stmt->bindParam(':usuario', $_SESSION['usuario']);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  echo "Error: Usuario no encontrado.";
  exit();
}

$role = $usuario['role'];
?>
<!doctype html>
<html
  lang="en"
  class="light-style layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../assets/"
  data-template="vertical-menu-template-free"
  data-style="light">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Demo : Dashboard - Analytics | sneat - Bootstrap Dashboard PRO</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="../../assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="../../assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="../../assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="../../assets/vendor/libs/apex-charts/apex-charts.css" />

  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="../../assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="../../assets/js/config.js"></script>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->

        <!-- / Navbar -->

        <!-- Menu lateral -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="" style="text-align: center; position: relative; z-index: 10;">
            <div class="app-brand-logo demo" style="display: flex; justify-content: center; flex-direction: column; align-items: center;">
              <img src="../../imagenes/logo.png" alt="Logo de la Empresa" style="width: 200px; height: auto;">
              <div class="app-brand-text demo menu-text fw-bolder" style="margin-top: 10px; font-size: 1.2rem; color: #495057;">
                BUSTILLOSFLIMAC SRL
              </div>
            </div>
          </div>

          <ul class="menu-inner py-1">
            <!-- Dashboard -->
            <li class="menu-item">
              <a href="../usuarios/dashboard_admin.php" class="menu-link">
                <i class="menu-icon bx bx-home-alt"></i>
                <div data-i18n="Analytics">Dashboard</div>
              </a>
            </li>
            <!-- Usuarios (solo para admin) -->
            <?php if ($role === 'admin' || $role === 'empleado'): ?>
              <!-- Usuarios -->
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon bx bx-user"></i>
                  <div data-i18n="Usuarios">Usuarios</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item">
                    <a href="../usuarios/usuarioRegistro.php" class="menu-link">
                      <div data-i18n="Registrar Usuario">Registrar Usuario</div>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="../usuarios/administradorLista.php" class="menu-link">
                      <div data-i18n="Listar Usuario">Listar Usuario</div>
                    </a>
                  </li>

                </ul>
              </li>
            <?php endif; ?>
            <!-- Trámites -->
            <!-- Usuarios (solo para admin) -->
            <?php if ($role === 'admin' || $role === 'cliente' || $role === 'empleado'): ?>
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon bx bx-file"></i>
                  <div data-i18n="Tramites">Trámites</div>
                </a>


                <ul class="menu-sub">
                  <?php if ($role === 'admin' || $role === 'empleado'): ?>
                    <li class="menu-item">
                      <a href="../tramites/tramiteRegistro.php" class="menu-link">
                        <div data-i18n="Registrar Trámite">Registrar Trámite</div>
                      </a>
                    </li>
                  <?php endif; ?>
                  <li class="menu-item">
                    <a href="../tramites/tramiteLista.php" class="menu-link">
                      <div data-i18n="Listar Trámite">Listar Trámite</div>
                    </a>
                  </li>

                </ul>
              </li>
            <?php endif; ?>
            <!-- Usuarios (solo para admin) -->
            <?php if ($role === 'admin'|| $role === 'empleado'): ?>
              <!-- Procesos -->
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon bx bx-cog"></i>
                  <div data-i18n="Procesos">Seguimientos</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item">

                  </li>
                  <li class="menu-item">
                    <a href="../procesos/procesoTodoLista.php" class="menu-link">
                      <div data-i18n="">Listar Seguimientos</div>
                    </a>
                  </li>

                </ul>
              </li>

            <?php endif; ?>

            <?php if ($role === 'admin'|| $role === 'empleado'): ?>
              <!-- reportes -->
              <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon bx bx-task"></i>
                  <div data-i18n="Reportes">Reportes</div>
                </a>
                <ul class="menu-sub">
                  <li class="menu-item">
                    <a href="../procesos/procesoVistaR.php" class="menu-link">
                      <div data-i18n="incidencias">Reporte por incidencias</div>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="../clientes/reportePorClienteV.php" class="menu-link">
                      <div data-i18n="Listar Seguimiento">Reporte de seguimiento por cliente</div>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="../procesos/reportePorFechaV.php" class="menu-link">
                      <div data-i18n="Listar Seguimiento">Reporte de ptocesos por Fecha</div>
                    </a>
                  </li>
                </ul>
              </li>
            <?php endif; ?>
          </ul>
        </aside>

        <!-- Navbar -->
        <nav
          class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
          id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
              <i class="bx bx-menu bx-md"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <!-- Search -->

            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <!-- Place this tag where you want the button to render. -->


              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a
                  class="nav-link dropdown-toggle hide-arrow p-0"
                  href="javascript:void(0);"
                  data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="../../imagenes/usuariodefault.png" alt class="w-px-40 h-auto rounded-circle" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">

                  <li>
                    <div class="dropdown-divider my-1"></div>
                  </li>
                  <li>
                    <div class="dropdown-divider my-1"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="../usuarios/logout.php">
                      <i class="bx bx-power-off bx-md me-3"></i><span>Salir Sesión</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>
        </nav>

        <!-- / Navbar -->