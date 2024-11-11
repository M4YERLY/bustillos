<?php
ob_start();
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir PHPMailer
require __DIR__ . '../../../PHPMailer/src/Exception.php';
require __DIR__ . '../../../PHPMailer/src/PHPMailer.php';
require __DIR__ . '../../../PHPMailer/src/SMTP.php';

// Incluir conexión a la base de datos
include("../../modelo/conexion.php");
include("../../componentes/header.php");

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener los parámetros
$proceso_id = $_GET['proceso_id'] ?? null;
$tramite_id = $_GET['tramite_id'] ?? null;
$cliente_id = $_GET['cliente_id'] ?? null;

if (!$proceso_id || !$tramite_id || !$cliente_id) {
    echo "Error: Parámetros insuficientes.";
    exit();
}

$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener datos del proceso
$stmt = $conn->prepare("
    SELECT p.nombre AS proceso_nombre, p.tipo AS proceso_tipo, ep.nombre AS estado_actual, ep.id AS estado_id, 
           t.nombre AS tramite_nombre, CONCAT(c.nombre, ' ', c.apellido_paterno) AS cliente_nombre, c.email AS cliente_email
    FROM procesos p
    JOIN tramites t ON p.tramite_id = t.id
    JOIN clientes c ON t.cliente_id = c.id
    JOIN detalle_tramite dt ON p.id = dt.proceso_id
    JOIN estado_proceso ep ON dt.estado_id = ep.id
    WHERE p.id = :proceso_id
");
$stmt->bindParam(':proceso_id', $proceso_id);
$stmt->execute();
$proceso = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener los estados disponibles
$stmt_estados = $conn->prepare("SELECT id, nombre FROM estado_proceso");
$stmt_estados->execute();
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);

// Mapear los estados por ID para facilitar el acceso
$estados_map = [];
foreach ($estados as $estado) {
    $estados_map[$estado['id']] = $estado['nombre'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_estado = $_POST['estado_id'];
    $informar_cliente = isset($_POST['informar_cliente']) ? true : false;

    try {
        // Actualizar estado en la base de datos
        $stmt_update = $conn->prepare("UPDATE detalle_tramite SET estado_id = :estado_id WHERE proceso_id = :proceso_id");
        $stmt_update->bindParam(':estado_id', $nuevo_estado);
        $stmt_update->bindParam(':proceso_id', $proceso_id);
        $stmt_update->execute();

        // Informar al cliente por correo si la opción está activada
        if ($informar_cliente) {
            $cliente_email = $proceso['cliente_email'];
            $cliente_nombre = $proceso['cliente_nombre'];
            $tramite_nombre = $proceso['tramite_nombre'];
            $proceso_nombre = $proceso['proceso_nombre'];
            $nuevo_estado_nombre = $estados_map[$nuevo_estado] ?? null;

            // Verificar que el estado sea válido
            if ($nuevo_estado_nombre) {
                $estado_normalizado = strtolower(trim($nuevo_estado_nombre));
                $mensaje = "";

                switch ($estado_normalizado) {
                    case "terminado":
                        $mensaje = "
                            <p>Estimado/a <b>$cliente_nombre</b>,</p>
                            <p>Nos complace informarle que el trámite <b>$tramite_nombre</b>, correspondiente al proceso <b>$proceso_nombre</b>, ha sido <b>concluido exitosamente</b>.</p>
                            <p>Puede acercarse a nuestras oficinas para cualquier documentación adicional que requiera.</p>
                            <p>Atentamente,<br>El equipo de BUSTILLOS FLIMAC SRL</p>
                        ";
                        break;
                    case "en proceso":
                        $mensaje = "
                            <p>Estimado/a <b>$cliente_nombre</b>,</p>
                            <p>Le informamos que el trámite <b>$tramite_nombre</b>, relacionado con el proceso <b>$proceso_nombre</b>, está actualmente en <b>proceso de gestión</b>.</p>
                            <p>Si tiene alguna consulta, no dude en visitarnos en nuestras oficinas.</p>
                            <p>Atentamente,<br>El equipo de BUSTILLOS FLIMAC SRL</p>
                        ";
                        break;
                    case "sin procesar":
                        $mensaje = "
                            <p>Estimado/a <b>$cliente_nombre</b>,</p>
                            <p>Lamentamos informarle que el trámite <b>$tramite_nombre</b>, correspondiente al proceso <b>$proceso_nombre</b>, se encuentra actualmente <b>sin procesar</b>.</p>
                            <p>Por favor, visite nuestras oficinas para resolver cualquier inconveniente.</p>
                            <p>Atentamente,<br>El equipo de BUSTILLOS FLIMAC SRL</p>
                        ";
                        break;
                    default:
                        $mensaje = "<p>Error inesperado al procesar el estado del trámite.</p>";
                }

                $asunto = "Actualizacion de Estado del Tramite-Proceso";
                enviarCorreo($cliente_email, $asunto, $mensaje);
            }
        }

        header("Location: procesoTodoLista.php");
        exit();
    } catch (Exception $e) {
        echo "Error al actualizar el estado: " . $e->getMessage();
    }
}

// Función para enviar correo
function enviarCorreo($destinatario, $asunto, $mensaje)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'maye0949@gmail.com'; // Correo de origen
        $mail->Password = 'saepmbhfwkhiaxgy';         // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Opciones SSL - (POSIBLEMENTE CAMBIAR)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            )
        );
        //////////////////////////////////////////////

        $mail->setFrom('maye0949@gmail.com', 'BUSTILLOS FLIMAC SRL - ESTADO DE PROCESO');
        $mail->addAddress($destinatario);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;

        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }
}
?>

<!-- Formulario de edición -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow-lg mx-auto col-md-6">
            <div class="card-body">
                <h4 class="text-primary text-center mb-4">Editar Estado del Proceso</h4>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($proceso['cliente_nombre']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trámite</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($proceso['tramite_nombre']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proceso</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($proceso['proceso_nombre']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($proceso['proceso_tipo']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado_id" class="form-select" required>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?= $estado['id'] ?>" <?= ($estado['id'] == $proceso['estado_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="informar_cliente" id="informar_cliente" checked>
                            <label class="form-check-label" for="informar_cliente">Informar al cliente sobre los cambios</label>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="procesoTodoLista.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include("../../componentes/footer.php"); ?>