<?php
session_start();
include("../../modelo/conexion.php");
include("../../componentes/header.php"); // Incluir el header

// Validar parámetros GET
if (!isset($_GET['tramite_id']) || !isset($_GET['cliente_id'])) {
    echo "<h3 class='text-danger text-center'>Parámetros inválidos. Asegúrese de acceder correctamente.</h3>";
    exit();
}

$tramiteId = htmlspecialchars($_GET['tramite_id']);
$clienteId = htmlspecialchars($_GET['cliente_id']);
?>
<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-lg">
            <div class="card-body">
                <h2 class="mb-8 text-center text-primary">
                    Selecciona el tipo de proceso a registrar
                </h2>

                <!-- Formulario con las tres opciones -->
                <form id="processTypeForm">
                    <div class="row mb-3 text-center">
                        <div class="col-12 col-sm-4">
                            <button type="button" class="btn btn-primary w-100" onclick="redirectProcess('naval')">
                                Proceso Naval
                            </button>
                        </div>
                        <div class="col-12 col-sm-4">
                            <button type="button" class="btn btn-success w-100" onclick="redirectProcess('aereo')">
                                Proceso Aéreo
                            </button>
                        </div>
                        <div class="col-12 col-sm-4">
                            <button type="button" class="btn btn-info w-100" onclick="redirectProcess('terrestre')">
                                Proceso Terrestre
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Content Wrapper -->

<?php include("../../componentes/footer.php"); ?>

<!-- Script para redirigir a la página de registro correspondiente -->
<script>
    function redirectProcess(processType) {
        const tramiteId = <?= json_encode($tramiteId) ?>;
        const clienteId = <?= json_encode($clienteId) ?>;

        if (processType === 'naval') {
            window.location.href = `procesoNavalRegistro.php?tramite_id=${tramiteId}&cliente_id=${clienteId}`;
        } else if (processType === 'aereo') {
            window.location.href = `procesoAereoRegistro.php?tramite_id=${tramiteId}&cliente_id=${clienteId}`;
        } else if (processType === 'terrestre') {
            window.location.href = `procesoTerrestreRegistro.php?tramite_id=${tramiteId}&cliente_id=${clienteId}`;
        }
    }
</script>
</body>
</html>
