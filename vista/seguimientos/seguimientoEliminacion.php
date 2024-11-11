<?php
session_start();
include("../../modelo/conexion.php");

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Verificar si se proporcionó un ID de seguimiento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Error: No se proporcionó un ID de seguimiento.";
    exit();
}

$seguimiento_id = htmlspecialchars($_GET['id']);

$conexion = new Conexion();
$conn = $conexion->conectar();

try {
    // Verificar si el seguimiento existe
    $stmt_verificar = $conn->prepare("SELECT id FROM seguimientos WHERE id = :id");
    $stmt_verificar->bindParam(':id', $seguimiento_id);
    $stmt_verificar->execute();

    if ($stmt_verificar->rowCount() === 0) {
        echo "Error: El seguimiento no existe.";
        exit();
    }

    // Eliminar el seguimiento
    $stmt_eliminar = $conn->prepare("DELETE FROM seguimientos WHERE id = :id");
    $stmt_eliminar->bindParam(':id', $seguimiento_id);
    $stmt_eliminar->execute();

    // Redirigir de vuelta a la lista de seguimientos
    header("Location: seguimientoLista.php");
    exit();
} catch (Exception $e) {
    echo "Error al eliminar el seguimiento: " . $e->getMessage();
    exit();
}
?>
