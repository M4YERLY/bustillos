<?php
function getUserRole($username)
{
    // Conexión a la base de datos
    include_once("../../modelo/conexion.php");

    $conexion = new Conexion();
    $conn = $conexion->conectar();

    // Consulta para obtener el rol del usuario
    $stmt = $conn->prepare("SELECT role FROM usuarios WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retornar el rol si se encuentra
    return $user ? $user['role'] : null;
}
