<?php
session_start();
include("../../modelo/conexion.php");

$id = $_GET['id'];

$conexion = new Conexion();
$conn = $conexion->conectar();
$stmt = $conn->prepare("DELETE FROM administradores WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

header("Location: administradorLista.php?success=delete");
