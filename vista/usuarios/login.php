<?php
session_start();
include("../../modelo/conexion.php");

// Verificar si se ha enviado el formulario
if (isset($_POST['ingresar'])) {
  $email_or_username = htmlspecialchars($_POST['usr']);
  $password = htmlspecialchars($_POST['pwd']);

  // Conectar a la base de datos
  $conexion = new Conexion();
  $conn = $conexion->conectar();

  // Consultar si el usuario existe por correo o nombre de usuario
  $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = :email_or_username OR email = :email_or_username");
  $stmt->bindParam(':email_or_username', $email_or_username);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Verificar si el usuario fue encontrado y si la contraseña es correcta
  if ($user && password_verify($password, $user['password'])) {
    // Guardar en sesión el rol del usuario y su nombre de usuario
    $_SESSION['usuario'] = $user['username'];
    $_SESSION['rol'] = $user['role'];

    // Redirigir al dashboard según el rol
    if ($user['role'] == 'admin') {
      header("Location: ../../vista/usuarios/dashboard_admin.php");
    } elseif ($user['role'] == 'cliente') {
      header("Location: ../../vista/clientes/dashboard_cliente.php");
    } elseif ($user['role'] == 'empleado') {
      header("Location: ../../vista/empleados/dashboard_empleado.php");
    }
    exit();
  } else {
    // Error: Usuario o contraseña incorrectos
    header("Location: login.php?error=1");
    exit();
  }
}
?>


<!doctype html>
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Login - BustillosFlimac</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="../../assets/vendor/fonts/boxicons.css" />
  <link rel="stylesheet" href="../../assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="../../assets/css/demo.css" />

  <!-- Page CSS -->
  <link rel="stylesheet" href="../../assets/vendor/css/pages/page-auth.css" />

  <!-- Helpers -->
  <script src="../../assets/vendor/js/helpers.js"></script>
</head>

<body>
  <!-- Content -->
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Login Card -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
              <a href="index.php" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                  <!-- Logo SVG similar a Sneat Bootstrap -->
                  <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs>
                      <path d="M13.7918663,0.358365126 L3.39788168,7.44174259 ..." id="path-1"></path>
                      <path d="M5.47320593,6.00457225 C4.05321814,8.216144 ..." id="path-3"></path>
                      <path d="M7.50063644,21.2294429 L12.3234468,23.3159332 ..." id="path-4"></path>
                      <path d="M20.6,7.13333333 L25.6,13.8 ..." id="path-5"></path>
                    </defs>
                    <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                      <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                        <g id="Icon" transform="translate(27.000000, 15.000000)">
                          <g id="Mask" transform="translate(0.000000, 8.000000)">
                            <mask id="mask-2" fill="white">
                              <use xlink:href="#path-1"></use>
                            </mask>
                            <use fill="#696cff" xlink:href="#path-1"></use>
                            <g id="Path-3" mask="url(#mask-2)">
                              <use fill="#696cff" xlink:href="#path-3"></use>
                              <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                            </g>
                          </g>
                        </g>
                      </g>
                    </g>
                  </svg>
                </span>
                <span class="app-brand-text demo text-heading fw-bold">BustillosFlimac</span>
              </a>
            </div>
            <!-- /Logo -->

            <h4 class="mb-1 pt-2">Bienvenido! 👋</h4>
            <p class="mb-4">Por favor ingresa tu cuenta para empezar</p>

            <!-- Login Form -->
            <form method="post" action="">
              <div class="mb-3">
                <label for="usr" class="form-label">Email o Nombre de Usuario</label>
                <input type="text" class="form-control" id="usr" name="usr" placeholder="Ingresa tu email o usuario" autofocus required />
              </div>
              <div class="mb-3 form-password-toggle">
                <label class="form-label" for="pwd">Contraseña</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="pwd" class="form-control" name="pwd" placeholder="********" required />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>

              <!-- <div class="mb-4 d-flex justify-content-between">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember-me" />
                  <label class="form-check-label" for="remember-me"> Recordarme </label>
                </div>
                <a href="auth-forgot-password-basic.html"><span>Olvidaste tu contraseña?</span></a>
              </div>-->

              <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" type="submit" name="ingresar">Ingresar</button>
              </div>
            </form>
            <!-- /Login Form -->

            <?php
            if (isset($_GET['error'])) {
              echo "<div class='alert alert-danger'>Usuario o contraseña incorrectos</div>";
            }
            ?>

            <!-- <p class="text-center">
                            <span>¿Nuevo en nuestra plataforma?</span>
                            <a href="auth-register-basic.html"><span>Crear una cuenta</span></a>
                        </p> -->
          </div>
        </div>
        <!-- /Login Card -->
      </div>
    </div>
  </div>
  <!-- /Content -->

  <!-- Core JS -->
  <script src="../../assets/vendor/libs/jquery/jquery.js"></script>
  <script src="../../assets/vendor/libs/popper/popper.js"></script>
  <script src="../../assets/vendor/js/bootstrap.js"></script>
  <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="../../assets/vendor/js/menu.js"></script>
  <script src="../../assets/js/main.js"></script>
</body>

</html>