<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir PHPMailer
require __DIR__ . '../../../PHPMailer/src/Exception.php';
require __DIR__ . '../../../PHPMailer/src/PHPMailer.php';
require __DIR__ . '../../../PHPMailer/src/SMTP.php';
require("../../modelo/conexion.php");

// Conexión a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Verificar datos recibidos
$proceso_id = $_POST['proceso_id'] ?? null;
$tramite_id = $_POST['tramite_id'] ?? null;
$cliente_id = $_POST['cliente_id'] ?? null;

if (!$proceso_id || !$tramite_id || !$cliente_id) {
    echo "Error: Datos insuficientes para enviar notificación.";
    exit();
}

// Obtener información del cliente y proceso
$stmt = $conn->prepare("
    SELECT c.nombre AS cliente_nombre, c.email AS cliente_email, 
           t.nombre AS tramite_nombre, p.nombre AS proceso_nombre,p.tipo AS tipo
    FROM clientes c
    JOIN tramites t ON c.id = t.cliente_id
    JOIN procesos p ON t.id = p.tramite_id
    WHERE p.id = :proceso_id AND c.id = :cliente_id
");
$stmt->bindParam(':proceso_id', $proceso_id);
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos) {
    echo "Error: No se encontró información del cliente o proceso.";
    exit();
}

try {
    // Datos del correo
    $cliente_email = $datos['cliente_email'];
    $cliente_nombre = $datos['cliente_nombre'];
    $tramite_nombre = $datos['tramite_nombre'];
    $proceso_nombre = $datos['tipo'];
    $asunto = "Notificación: Documentación Pendiente";
    $mensaje = "
        <p>Estimado/a <b>$cliente_nombre</b>,</p>
        <p>Le informamos que en el trámite <b>$tramite_nombre</b>, correspondiente al proceso <b>$proceso_nombre</b>, 
        aún no se han subido los documentos necesarios.</p>
        <p>Le solicitamos amablemente completar esta información lo antes posible.</p>
        <p>Atentamente,<br>El equipo de BUSTILLOS FLIMAC SRL</p>
    ";

    // Enviar correo
    enviarCorreo($cliente_email, $asunto, $mensaje);

    // Redirigir tras éxito
    header("Location: procesoLista.php?tramite_id=$tramite_id&cliente_id=$cliente_id");
    exit();
} catch (Exception $e) {
    echo "Error al enviar el correo: " . $e->getMessage();
}

// Función de envío de correo
function enviarCorreo($destinatario, $asunto, $mensaje)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'maye0949@gmail.com'; // Correo de origen
        $mail->Password = 'saepmbhfwkhiaxgy';  // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Opciones SSL
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            )
        );

        $mail->setFrom('maye0949@gmail.com', 'BUSTILLOS FLIMAC SRL - Notificación');
        $mail->addAddress($destinatario);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;

        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }
}
