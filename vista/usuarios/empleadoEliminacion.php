<?php
session_start();
include("../../modelo/conexion.php"); // Incluir la conexión a la base de datos

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Verificar si se ha enviado un ID para eliminar
if (isset($_GET['id'])) {
    $id = htmlspecialchars($_GET['id']);

    try {
        // Eliminar el empleado y su usuario relacionado
        $conn->beginTransaction();

        // Obtener el ID del usuario
        $stmt = $conn->prepare("SELECT usuario_id FROM empleados WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario_id = $empleado['usuario_id'];

        // Eliminar el empleado
        $stmt1 = $conn->prepare("DELETE FROM empleados WHERE id = :id");
        $stmt1->bindParam(':id', $id);
        $stmt1->execute();

        // Eliminar el usuario relacionado
        $stmt2 = $conn->prepare("DELETE FROM usuarios WHERE id = :usuario_id");
        $stmt2->bindParam(':usuario_id', $usuario_id);
        $stmt2->execute();

        $conn->commit();

        // Redirigir a la lista después de eliminar
        echo "<script>window.location.href = 'administradorLista.php?success=3';</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error al eliminar: " . $e->getMessage();
    }
}
?>
