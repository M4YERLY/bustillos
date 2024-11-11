<?php
session_start();
include("../../modelo/conexion.php");
include("../../componentes/header.php"); // Incluir el header
?>
<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Card de Selección de Usuario -->
        <div class="card shadow-lg">
            <div class="card-body">
                <h4 class="mb-8 text-center text-primary">
                    Selecciona el tipo de usuario a registrar
                </h4>
                
                <!-- Formulario con las tres opciones -->
                <form id="userTypeForm">
                    <div class="row mb-3 text-center">
                        <div class="col-12 col-sm-4">
                            <button type="button" class="btn btn-primary w-100" onclick="redirectUser('admin')">
                                Registrar Administrador
                            </button>
                        </div>
                        <div class="col-12 col-sm-4">
                            <button type="button" class="btn btn-success w-100" onclick="redirectUser('empleado')">
                                Registrar Empleado
                            </button>
                        </div>
                        <div class="col-12 col-sm-4">
                            <button type="button" class="btn btn-info w-100" onclick="redirectUser('cliente')">
                                Registrar Cliente
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- /Card de Selección de Usuario -->
    </div>
</div>
<!-- /Content Wrapper -->

<?php include("../../componentes/footer.php"); ?>

<!-- Script para redirigir a la página de registro correspondiente -->
<script>
    function redirectUser(userType) {
        if (userType === 'admin') {
            window.location.href = 'administradorRegistro.php';
        } else if (userType === 'empleado') {
            window.location.href = 'empleadoRegistro.php';
        } else if (userType === 'cliente') {
            window.location.href = 'clienteRegistro.php';
        }
    }
</script>
