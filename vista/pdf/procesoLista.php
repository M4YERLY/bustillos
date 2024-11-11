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

// Obtener la lista de procesos
$query = "
    SELECT c.nombre AS cliente_nombre, p.nombre AS proceso_nombre, p.tipo AS proceso_tipo, ep.nombre AS estado_nombre, e.nombre AS empleado_nombre
    FROM procesos p
    JOIN tramites t ON p.tramite_id = t.id
    JOIN clientes c ON t.cliente_id = c.id
    JOIN detalle_tramite dt ON p.id = dt.proceso_id
    JOIN empleados e ON dt.empleado_id = e.id
    JOIN estado_proceso ep ON p.estado_id = ep.id
";
$stmt = $conn->prepare($query);
$stmt->execute();
$procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear PDF en formato horizontal
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Encabezado
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 102, 204); // Azul vibrante
$pdf->Cell(0, 15, 'Lista de Todos los Procesos', 0, 1, 'C');

// Línea decorativa
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(1.5);
$pdf->Line(10, 25, 287, 25);

// Espaciado
$pdf->Ln(10);

// Configuración de colores de la tabla
$pdf->SetFillColor(0, 102, 204); // Azul brillante
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->SetDrawColor(0, 102, 204); // Bordes
$pdf->SetLineWidth(0.5);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 12, 'Cliente', 1, 0, 'C', true);
$pdf->Cell(60, 12, 'Proceso', 1, 0, 'C', true);
$pdf->Cell(30, 12, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(30, 12, 'Estado', 1, 0, 'C', true);
$pdf->Cell(60, 12, 'Empleado', 1, 1, 'C', true);

// Restablecer colores para las filas
$pdf->SetFillColor(240, 240, 240); // Gris claro para filas alternadas
$pdf->SetTextColor(0); // Texto negro
$pdf->SetFont('Arial', '', 10);

// Alternar color de las filas
$fill = false;

foreach ($procesos as $proceso) {
    $pdf->Cell(60, 10, utf8_decode($proceso['cliente_nombre']), 1, 0, 'L', $fill);
    $pdf->Cell(60, 10, utf8_decode($proceso['proceso_nombre']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, utf8_decode($proceso['proceso_tipo']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, utf8_decode($proceso['estado_nombre']), 1, 0, 'L', $fill);
    $pdf->Cell(60, 10, utf8_decode($proceso['empleado_nombre']), 1, 1, 'L', $fill);
    $fill = !$fill; // Alternar color de las filas
}

// Pie de página con información de fecha y hora
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(100, 100, 100); // Gris claro
$pdf->Cell(0, 10, 'Generado el ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Salida del archivo PDF
$pdf->Output('I', 'lista_procesos.pdf');
?>
