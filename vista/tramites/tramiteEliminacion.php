<?php
ob_start();
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
        // Iniciar una transacción
        $conn->beginTransaction();

        // Eliminar seguimientos relacionados
        $stmt_seguimientos = $conn->prepare("DELETE FROM seguimientos WHERE tramite_id = :id");
        $stmt_seguimientos->bindParam(':id', $id);
        $stmt_seguimientos->execute();

        // Eliminar documentos relacionados
        $stmt_documentos = $conn->prepare("DELETE FROM documentos WHERE tramite_id = :id");
        $stmt_documentos->bindParam(':id', $id);
        $stmt_documentos->execute();

        // Eliminar el trámite
        $stmt_tramite = $conn->prepare("DELETE FROM tramites WHERE id = :id");
        $stmt_tramite->bindParam(':id', $id);
        $stmt_tramite->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir después de la eliminación
        header("Location: tramiteLista.php?success=2"); // Redirigir a la lista con un mensaje de éxito
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        echo "Error al eliminar el trámite: " . $e->getMessage();
    }
} else {
    echo "ID de trámite no proporcionado.";
}
