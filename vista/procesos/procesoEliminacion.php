<?php
ob_start();
session_start();
include("../../modelo/conexion.php"); // Incluir la conexión a la base de datos

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener los parámetros necesarios para eliminar el proceso
$proceso_id = $_GET['proceso_id'] ?? null;
$tramite_id = $_GET['tramite_id'] ?? null;
$cliente_id = $_GET['cliente_id'] ?? null;

if (!$proceso_id || !$tramite_id || !$cliente_id) {
    echo "Error: Proceso, Trámite o Cliente no especificado.";
    exit();
}

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

try {
    // Iniciar una transacción para eliminar el proceso
    $conn->beginTransaction();

    // Eliminar primero los registros relacionados en la tabla `detalle_tramite`
    $stmt_detalle = $conn->prepare("DELETE FROM detalle_tramite WHERE proceso_id = :proceso_id");
    $stmt_detalle->bindParam(':proceso_id', $proceso_id);
    $stmt_detalle->execute();

    // Eliminar el proceso de la tabla `procesos`
    $stmt_proceso = $conn->prepare("DELETE FROM procesos WHERE id = :proceso_id");
    $stmt_proceso->bindParam(':proceso_id', $proceso_id);
    $stmt_proceso->execute();

    // Confirmar la transacción
    $conn->commit();

    // Redirigir a la lista de procesos con un mensaje de éxito
    header("Location: procesoLista.php?tramite_id=$tramite_id&cliente_id=$cliente_id&eliminado=1");
    exit();
} catch (Exception $e) {
    // Si hay un error, hacer un rollback y mostrar el error
    $conn->rollBack();
    echo "Error al eliminar el proceso: " . $e->getMessage();
    exit();
}
