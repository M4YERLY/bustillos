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
        // Iniciar transacción
        $conn->beginTransaction();

        // Verificar si el cliente tiene trámites o procesos asociados
        $stmt_verificar = $conn->prepare("
            SELECT COUNT(*) AS total_tramites 
            FROM tramites 
            WHERE cliente_id = :cliente_id
        ");
        $stmt_verificar->bindParam(':cliente_id', $id);
        $stmt_verificar->execute();
        $resultado = $stmt_verificar->fetch(PDO::FETCH_ASSOC);

        if ($resultado['total_tramites'] > 0) {
            // Si el cliente tiene trámites asociados, no se permite eliminar
            echo "<script>
                alert('El cliente tiene trámites o procesos en espera y no puede ser eliminado.');
                window.location.href = 'administradorLista.php';
            </script>";
            exit();
        }

        // Obtener el ID del usuario relacionado con el cliente
        $stmt = $conn->prepare("SELECT usuario_id FROM clientes WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario_id = $cliente['usuario_id'];

        // Eliminar el cliente
        $stmt1 = $conn->prepare("DELETE FROM clientes WHERE id = :id");
        $stmt1->bindParam(':id', $id);
        $stmt1->execute();

        // Eliminar el usuario relacionado
        $stmt2 = $conn->prepare("DELETE FROM usuarios WHERE id = :usuario_id");
        $stmt2->bindParam(':usuario_id', $usuario_id);
        $stmt2->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir a la lista después de eliminar
        echo "<script>window.location.href = 'administradorLista.php?success=3';</script>";
    } catch (Exception $e) {
        // En caso de error, deshacer la transacción
        $conn->rollBack();
        echo "<script>
            alert('Ocurrió un error al intentar eliminar el cliente: " . $e->getMessage() . "');
            window.location.href = 'administradorLista.php';
        </script>";
    }
} else {
    // Si no se envía ID, redirigir a la lista de clientes
    echo "<script>window.location.href = 'administradorLista.php';</script>";
    exit();
}
?>
