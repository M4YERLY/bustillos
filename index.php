<!doctype html>

<html
  lang="en"
  class="light-style layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  daassets-path="assets/"
  data-template="vertical-menu-template-free"
  data-style="light">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Demo : Without menu - Layouts | sneat - Bootstrap Dashboard PRO</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
  <link rel="stylesheet" media="all" href="assets/style.css" />
  <!-- Core CSS -->
  <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="assets/css/demo.css" />
  <link rel="stylesheet" href="assets/style.css" />
  <!-- Vendors CSS -->
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="assets/js/config.js"></script>
  <style>
  .title-large {
  font-size: 18rem; /* Ajusta el tamaño según lo necesario */
  font-weight: bold; /* Hace el texto más grueso */
  line-height: 1.1; /* Ajusta la separación entre líneas */
  text-transform: uppercase; /* Asegura todo en mayúsculas */
  text-align: center; /* Centra el texto */

  /* Contenedor principal */
.styled-section {
  padding: 30px;
  background-color: #f5f5f5; /* Fondo claro */
  border-radius: 8px; /* Bordes redondeados */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra sutil */
}

/* Título principal */
.section-title {
  font-size: 3rem; /* Tamaño grande para el título */
  font-weight: bold;
  color: #4e54c8; /* Color destacado */
  text-transform: uppercase;
  margin-bottom: 20px;
}

/* Estilos para "Misión" y "Visión" */
.mission-text, .vision-text {
  font-size: 1.8rem; /* Texto más grande */
  line-height: 1.6; /* Espaciado entre líneas */
  color: #333; /* Color oscuro para legibilidad */
  margin-bottom: 20px;
}

.mission-text strong, .vision-text strong {
  font-size: 2rem; /* Resalta los encabezados "Misión" y "Visión" */
  color: #4e54c8; /* Mismo color que el título */
}/* Contenedor principal */
.styled-section {
  display: flex;
  justify-content: center; /* Centra horizontalmente */
  align-items: center; /* Centra verticalmente */
  height: 100vh; /* Ocupa toda la altura de la ventana */
  background-color: #f5f5f5; /* Fondo claro */
  border-radius: 8px; /* Bordes redondeados */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra sutil */
}

/* Contenedor principal para centrar todo */
.centered-container {
  display: flex;
  justify-content: center; /* Centra horizontalmente */
  align-items: center; /* Centra verticalmente */
  height: 100vh; /* Ocupa toda la ventana */
  background-color: #f5f5f5; /* Fondo claro */
  text-align: center; /* Centra el texto */
}

/* Contenido de texto */
.text-content {
  max-width: 800px; /* Limita el ancho máximo del texto */
}

/* Estilo de texto */
.mission-text, .vision-text {
  font-size: 8rem; /* Texto grande */
  line-height: 1.6; /* Espaciado entre líneas */
  color: #333; /* Color oscuro para legibilidad */
  margin-bottom: 20px;
}

.mission-text strong, .vision-text strong {
  font-size: 8rem; /* Texto destacado para "Misión" y "Visión" */
  color: #4e54c8; /* Color destacado */
}


</style>
</head>

<body>

  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar layout-without-menu">
    <div class="layout-container">
      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->

        <!-- Navbar: Start -->
        <nav class="layout-navbar shadow-none py-0">
          <div class="container">
            <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
              <!-- Menu logo wrapper: Start -->
              <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8">
                <!-- Mobile menu toggle: Start-->
                <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                  <i class="tf-icons bx bx-menu bx-lg align-middle text-heading fw-medium"></i>
                </button>
                <!-- Mobile menu toggle: End-->

              </div>
              <!-- Menu logo wrapper: End -->
              <!-- Menu wrapper: Start -->
              <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
  <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <i class="tf-icons bx bx-x bx-lg"></i>
  </button>
  <ul class="navbar-nav me-auto">
    <li class="nav-item">
      <a class="nav-link fw-medium" href="#principal">Principal</a>
    </li>
    <li class="nav-item">
      <a class="nav-link fw-medium" href="#servicios">Servicios</a>
    </li>
    <li class="nav-item">
      <a class="nav-link fw-medium" href="#contacto">Contacto</a>
    </li>
  </ul>
</div>

              <div class="landing-menu-overlay d-lg-none"></div>
              <!-- Menu wrapper: End -->
              <!-- Toolbar: Start -->
              <ul class="navbar-nav flex-row align-items-center ms-auto">


                <!-- / Style Switcher-->

                <!-- navbar button: Start -->
                <li>
                  <a href="vista/usuarios/login.php" class="btn btn-primary" target="_blank"><span class="tf-icons bx bx-log-in-circle scaleX-n1-rtl me-md-1"></span><span class="d-none d-md-block">Inicio de Sesion</span></a>
                </li>
                <!-- navbar button: End -->
              </ul>
              <!-- Toolbar: End -->
            </div>
          </div>
        </nav>
        <!-- Navbar: End -->

        <!-- / Navbar -->
        <body>
  <!-- Fondo animado -->
  <div class="area">
    <ul class="circles">
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
    </ul>
  </div>

  <!-- Contenido principal -->
  <div class="context">
    <h3 class="cta-title text-warning text-center">BustillosFlimac SRL</h3>
    <h1 class="cta-title text-white text-center title-large">
  ¡TU CARGA, NUESTRA PRIORIDAD: EFICIENCIA SIN FRONTERAS!
</h1>

  </div>
<div class="row">
  <section id="servicios" class="section-py landing-features">
  <div class="container styled-section">
  <div class="content-wrapper">
    <h2 class="text-center section-title">Nuestros Servicios</h2>
    <div class="text-content">
    <p class="mission-text">
      <strong>Misión:</strong> La misión de la agencia despachante de aduanas "BustillosFlimac SRL" es brindar un servicio excepcional a nuestros clientes, facilitando los procesos de importación y exportación de mercancías con eficiencia y cumpliendo con todas las normativas legales. Nos comprometemos a mejorar continuamente nuestros servicios, proporcionando información precisa y oportuna, y trabajando para reducir riesgos y dificultades en el comercio internacional. Aspiramos a expandir nuestra presencia para llegar a más lugares y ofrecer nuestros servicios a una mayor cantidad de importadores.
    </p>
    <p class="vision-text">
      <strong>Visión:</strong> Nuestra visión es ser la agencia despachante de aduanas líder en el mercado, reconocida por nuestra eficiencia, transparencia y calidad de servicio. Buscamos innovar constantemente en nuestros procesos mediante el uso de tecnologías avanzadas, logrando una coordinación óptima entre todas las partes involucradas en los trámites aduaneros. Pretendemos establecer una red amplia y confiable que permita a nuestros clientes realizar sus operaciones comerciales con confianza y seguridad, impulsando el crecimiento y desarrollo del comercio internacional.
    </p>
  </div>

  <!-- Useful features: Start -->
  <section id="landingFeatures" class="section-py landing-features">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary">Useful Features</span>
      </div>
      <h4 class="text-center mb-1">
        <span class="position-relative fw-extrabold z-1">Everything you need
        </span>
        to start your next project
      </h4>
      <p class="text-center mb-12">Not just a set of tools, the package includes ready-to-deploy conceptual application.</p>
      <div class="features-icon-wrapper row gx-0 gy-6 g-sm-12">
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="text-center mb-4">
            <img src="assets/img/ptin/p1.png"/>
          </div>
          <h5 class="mb-2">Quality Code</h5>
          <p class="features-icon-description">Code structure that all developers will easily understand and fall in love with.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="text-center mb-4">
            <img src="assets/img/ptin/p2.png" alt="transition up" />
          </div>
          <h5 class="mb-2">Continuous Updates</h5>
          <p class="features-icon-description">Free updates for the next 12 months, including new demos and features.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="text-center mb-4">
            <img src="assets/img/ptin/p3.png" alt="edit" />
          </div>
          <h5 class="mb-2">Stater-Kit</h5>
          <p class="features-icon-description">Start your project quickly without having to remove unnecessary features.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="text-center mb-4">
            <img src="assets/img/ptin/p4.png" alt="3d select solid" />
          </div>
          <h5 class="mb-2">API Ready</h5>
          <p class="features-icon-description">Just change the endpoint and see your own data loaded within seconds.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="text-center mb-4">
            <img src="assets/img/ptin/p5.png" alt="user" />
          </div>
          <h5 class="mb-2">Excellent Support</h5>
          <p class="features-icon-description">An easy-to-follow doc with lots of references and code examples.</p>
        </div>
        <div class="col-lg-4 col-sm-6 text-center features-icon-box">
          <div class="text-center mb-4">
            <img src="assets/img/ptin/p6.png" alt="keyboard" />
          </div>
          <h5 class="mb-2">Well Documented</h5>
          <p class="features-icon-description">An easy-to-follow doc with lots of references and code examples.</p>
        </div>
      </div>
    </div>
  </section>
  <!-- Useful features: End -->
</div>
</div>
  </section>

  <div class="row">
  <section id="contacto" class="section-py bg-body landing-contact">
  <footer class="landing-footer bg-body footer-text">
    <div class="container">
      <h2 class="text-center">Contáctanos</h2>
      <p class="text-center">Déjanos un mensaje y nos pondremos en contacto contigo.</p>
    </div>
  </section>
</footer>
  </div>
</body>

  

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  <script src="../../assets/vendor/libs/popper/popper.js"></script>
  <script src="../../assets/vendor/js/bootstrap.js"></script>
  
  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="../../assets/vendor/libs/nouislider/nouislider.js"></script>
<script src="../../assets/vendor/libs/swiper/swiper.js"></script>

  <!-- Main JS -->
  <script src="../../assets/js/front-main.js"></script>
  

  <!-- Page JS -->
  <script src="../../assets/js/front-page-landing.js"></script>
  
</body>

</html>

<!-- beautify ignore:end -->

