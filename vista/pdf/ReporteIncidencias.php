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

// Extender la clase FPDF
class PDF extends FPDF
{
    protected $widths;
    protected $aligns;

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, $data[$i], 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
}

// Conexión a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Verificar si el estado fue enviado
if (!isset($_GET['estado_id']) || empty($_GET['estado_id'])) {
    die("Error: No se seleccionó un estado.");
}

$estado_id = $_GET['estado_id'];

// Consulta para obtener trámites según el estado seleccionado
$stmt = $conn->prepare("
    SELECT t.id AS tramite_id, t.nombre AS tramite_nombre, t.descripcion, t.fecha,
           et.nombre AS estado_nombre, c.nombre AS cliente_nombre, c.apellido_paterno AS cliente_apellido, t.tipo
    FROM tramites t
    JOIN clientes c ON t.cliente_id = c.id
    JOIN estado_tramite et ON t.estado_id = et.id
    WHERE et.id = :estado_id
");
$stmt->bindParam(':estado_id', $estado_id);
$stmt->execute();
$tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Validar si hay resultados
if (count($tramites) === 0) {
    die("No se encontraron trámites para el estado seleccionado.");
}

// Crear el PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Encabezado
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'Reporte de Trámites - Estado: ' . $tramites[0]['estado_nombre']), 0, 1, 'C');
$pdf->Ln(10);

// Tabla de contenido
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetWidths([50, 50, 50, 40]);
$pdf->SetAligns(['L', 'L', 'C', 'C']);
$pdf->Row(['Nombre', 'Cliente', 'Fecha', 'Tipo']); // Encabezado

$pdf->SetFont('Arial', '', 10);
foreach ($tramites as $tramite) {
    $pdf->Row([
        iconv('UTF-8', 'ISO-8859-1', $tramite['tramite_nombre']),
        iconv('UTF-8', 'ISO-8859-1', $tramite['cliente_nombre'] . ' ' . $tramite['cliente_apellido']),
        $tramite['fecha'],
        iconv('UTF-8', 'ISO-8859-1', $tramite['tipo']),
    ]);
}

// Salida del PDF
$pdf->Output('I', 'reporte_tramites.pdf');
