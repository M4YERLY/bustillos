<?php
ob_start();
session_start();
include("../../componentes/rol.php");
require("../../modelo/conexion.php");
require('../../fpdf/fpdf.php');

// Verificar el rol del usuario actual
$username = $_SESSION['usuario'] ?? null;

if (!$username) {
    header("Location: login.php");
    exit();
}

$role = getUserRole($username);

// Conectar a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener la lista de administradores
$stmt_admin = $conn->prepare("
    SELECT a.id, a.nombre, a.apellido_paterno, a.apellido_materno, a.email, a.telefono, u.username 
    FROM administradores a 
    JOIN usuarios u ON a.usuario_id = u.id
");
$stmt_admin->execute();
$administradores = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de empleados
$stmt_emp = $conn->prepare("
    SELECT e.id, e.nombre, e.apellido_paterno, e.apellido_materno, e.email, e.telefono, e.salario, u.username 
    FROM empleados e 
    JOIN usuarios u ON e.usuario_id = u.id
");
$stmt_emp->execute();
$empleados = $stmt_emp->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de clientes
$stmt_client = $conn->prepare("
    SELECT c.id, c.nombre, c.apellido_paterno, c.apellido_materno, c.email, c.telefono, u.username 
    FROM clientes c 
    JOIN usuarios u ON c.usuario_id = u.id
");
$stmt_client->execute();
$clientes = $stmt_client->fetchAll(PDO::FETCH_ASSOC);

// Crear el PDF en horizontal
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Encabezado del PDF
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 102, 204); // Azul vibrante
$pdf->Cell(0, 15, 'Lista de Usuarios', 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(1.5);
$pdf->Line(10, 25, 287, 25); // Línea decorativa

$pdf->Ln(10);

// Sección de Administradores
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(0, 0, 0); // Negro
$pdf->Cell(0, 10, 'Administradores', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(0, 102, 204);
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->Cell(60, 10, 'Nombre Completo', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Telefono', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Usuario', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$fill = false;
foreach ($administradores as $admin) {
    $pdf->Cell(60, 10, utf8_decode($admin['nombre'] . ' ' . $admin['apellido_paterno'] . ' ' . $admin['apellido_materno']), 1, 0, 'L', $fill);
    $pdf->Cell(70, 10, utf8_decode($admin['email']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, $admin['telefono'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, utf8_decode($admin['username']), 1, 1, 'L', $fill);
    $fill = !$fill;
}

// Sección de Empleados
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Empleados', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(0, 102, 204);
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->Cell(60, 10, 'Nombre Completo', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Telefono', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Usuario', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Salario', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$fill = false;
foreach ($empleados as $emp) {
    $pdf->Cell(60, 10, utf8_decode($emp['nombre'] . ' ' . $emp['apellido_paterno'] . ' ' . $emp['apellido_materno']), 1, 0, 'L', $fill);
    $pdf->Cell(70, 10, utf8_decode($emp['email']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, $emp['telefono'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, utf8_decode($emp['username']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, number_format($emp['salario'], 2), 1, 1, 'L', $fill);
    $fill = !$fill;
}

// Sección de Clientes
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Clientes', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(0, 102, 204);
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->Cell(60, 10, 'Nombre Completo', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Telefono', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Usuario', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$fill = false;
foreach ($clientes as $client) {
    $pdf->Cell(60, 10, utf8_decode($client['nombre'] . ' ' . $client['apellido_paterno'] . ' ' . $client['apellido_materno']), 1, 0, 'L', $fill);
    $pdf->Cell(70, 10, utf8_decode($client['email']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, $client['telefono'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, utf8_decode($client['username']), 1, 1, 'L', $fill);
    $fill = !$fill;
}

// Pie de página con fecha y hora
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(100, 100, 100); // Gris claro
$pdf->Cell(0, 10, 'Generado el ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Salida del archivo PDF
$pdf->Output('I', 'lista_usuarios.pdf'); // El 'I' es para visualizar en el navegador.

?>
