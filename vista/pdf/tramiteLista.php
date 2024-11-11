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

// Obtener la lista de trámites
$stmt = $conn->prepare("
    SELECT t.id, t.nombre, t.descripcion, t.fecha, et.nombre AS estado_nombre, c.nombre AS cliente_nombre, c.apellido_paterno AS cliente_apellido, t.tipo
    FROM tramites t 
    JOIN clientes c ON t.cliente_id = c.id
    JOIN estado_tramite et ON t.estado_id = et.id
");
$stmt->execute();
$tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear un nuevo documento PDF en horizontal (L = Landscape)
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Encabezado personalizado
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 102, 204); // Azul vibrante
$pdf->Cell(0, 15, 'Lista de Tramites', 0, 1, 'C');

// Agregar una línea decorativa con más estilo
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(1.5);
$pdf->Line(10, 25, 287, 25); // Línea más larga para formato horizontal

// Espaciado
$pdf->Ln(10);

// Colores para la tabla
$pdf->SetFillColor(0, 102, 204); // Azul brillante para encabezados
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->SetDrawColor(0, 102, 204); // Azul oscuro para bordes
$pdf->SetLineWidth(0.5);

// Establecer la fuente para los encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);

// Encabezados de la tabla
$pdf->Cell(50, 12, 'Nombre del Tramite', 1, 0, 'C', true);
$pdf->Cell(100, 12, 'Descripcion', 1, 0, 'C', true);
$pdf->Cell(30, 12, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(40, 12, 'Cliente', 1, 0, 'C', true);
$pdf->Cell(30, 12, 'Estado', 1, 0, 'C', true);
$pdf->Cell(30, 12, 'Tipo', 1, 1, 'C', true);

// Restablecer colores para el contenido
$pdf->SetFillColor(240, 240, 240); // Gris claro para las filas
$pdf->SetTextColor(0); // Texto negro
$pdf->SetFont('Arial', '', 8);

// Alternar color de las filas
$fill = false;

// Recorrer los trámites y añadir cada uno a la tabla
foreach ($tramites as $tramite) {
    $pdf->Cell(50, 10, utf8_decode($tramite['nombre']), 1, 0, 'L', $fill);
    $pdf->Cell(100, 10, utf8_decode($tramite['descripcion']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, $tramite['fecha'], 1, 0, 'L', $fill);
    $pdf->Cell(40, 10, utf8_decode($tramite['cliente_nombre'] . ' ' . $tramite['cliente_apellido']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, utf8_decode($tramite['estado_nombre']), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, utf8_decode($tramite['tipo']), 1, 1, 'L', $fill);
    $fill = !$fill; // Alternar el color de las filas
}

// Pie de página con información de fecha y hora
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(100, 100, 100); // Gris claro
$pdf->Cell(0, 10, 'Generado el ' . date('d/m/Y H:i:s'), 0, 0, 'C');

// Salida del archivo PDF
$pdf->Output('I', 'lista_tramites.pdf'); // El 'I' es para visualizar en el navegador. Cambiar por 'D' para descargar.

?>
