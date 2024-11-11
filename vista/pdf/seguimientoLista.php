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

// Obtener la lista de seguimientos
$query = "
    SELECT s.id, t.nombre AS tramite_nombre, s.observaciones, s.fecha_seguimiento, e.nombre AS empleado_nombre, s.tipo_seguimiento
    FROM seguimientos s
    JOIN tramites t ON s.tramite_id = t.id
    JOIN empleados e ON s.empleado_id = e.id
";
$stmt = $conn->prepare($query);
$stmt->execute();
$seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear PDF en formato horizontal
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Encabezado del PDF
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 102, 204); // Azul vibrante
$pdf->Cell(0, 15, 'Lista de Seguimientos', 0, 1, 'C');

// Línea decorativa
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(1.5);
$pdf->Line(10, 25, 287, 25); // Línea completa para el formato horizontal

// Espaciado
$pdf->Ln(10);

// Colores y estilos para la tabla
$pdf->SetFillColor(0, 102, 204); // Azul brillante para encabezados
$pdf->SetTextColor(255, 255, 255); // Texto blanco para encabezados
$pdf->SetDrawColor(0, 102, 204); // Bordes
$pdf->SetLineWidth(0.5);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 12, 'Trámite', 1, 0, 'C', true);
$pdf->Cell(80, 12, 'Observaciones', 1, 0, 'C', true);
$pdf->Cell(40, 12, 'Fecha de Seguimiento', 1, 0, 'C', true);
$pdf->Cell(60, 12, 'Empleado', 1, 0, 'C', true);
$pdf->Cell(25, 12, 'Tipo', 1, 1, 'C', true);

// Restablecer colores para las filas
$pdf->SetFillColor(240, 240, 240); // Gris claro para las filas alternadas
$pdf->SetTextColor(0); // Texto negro
$pdf->SetFont('Arial', '', 10);

// Alternar el color de las filas
$fill = false;

// Añadir los datos a la tabla
foreach ($seguimientos as $seguimiento) {
    $pdf->Cell(70, 10, utf8_decode($seguimiento['tramite_nombre']), 1, 0, 'L', $fill);
    $pdf->Cell(80, 10, utf8_decode($seguimiento['observaciones']), 1, 0, 'L', $fill);
    $pdf->Cell(40, 10, $seguimiento['fecha_seguimiento'], 1, 0, 'L', $fill);
    $pdf->Cell(60, 10, utf8_decode($seguimiento['empleado_nombre']), 1, 0, 'L', $fill);
    $pdf->Cell(25, 10, utf8_decode($seguimiento['tipo_seguimiento']), 1, 1, 'L', $fill);
    $fill = !$fill; // Alternar el color de las filas
}

// Pie de página con fecha y hora de generación del PDF
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(100, 100, 100); // Texto en gris
$pdf->Cell(0, 10, 'Generado el ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Salida del archivo PDF
$pdf->Output('I', 'lista_seguimientos.pdf'); // 'I' para mostrar en el navegador
