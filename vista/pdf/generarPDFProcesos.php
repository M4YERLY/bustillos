<?php
require("../../modelo/conexion.php");
require("../../fpdf/fpdf.php");

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

if (!isset($_GET['tramite_id']) || !isset($_GET['cliente_id'])) {
    die("Error: No se especificaron los parámetros del trámite y cliente.");
}

$tramite_id = $_GET['tramite_id'];
$cliente_id = $_GET['cliente_id'];

$stmt = $conn->prepare("
    SELECT c.nombre AS cliente_nombre, t.nombre AS tramite_nombre, p.nombre AS proceso_nombre, p.descripcion AS proceso_descripcion, 
           e.nombre AS empleado_nombre, ep.nombre AS estado_proceso, dt.fecha_inicio, dt.fecha_fin,  p.tipo AS tipo
    FROM detalle_tramite dt
    JOIN tramites t ON dt.tramite_id = t.id
    JOIN clientes c ON t.cliente_id = c.id
    JOIN procesos p ON dt.proceso_id = p.id
    JOIN empleados e ON dt.empleado_id = e.id
    JOIN estado_proceso ep ON dt.estado_id = ep.id
    WHERE t.id = :tramite_id AND c.id = :cliente_id
");
$stmt->bindParam(':tramite_id', $tramite_id);
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($procesos) === 0) {
    die("No se encontraron procesos para el trámite y cliente seleccionados.");
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

// Título
$pdf->Cell(0, 10, utf8_decode('Reporte de Procesos'), 0, 1, 'C');
$pdf->Ln(10);

// Encabezado de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);

// Ajustar los anchos para que encajen en la página (suma total <= 270 mm)
$pdf->SetWidths([30, 30, 30, 90, 30, 20, 20,20]);
$pdf->Row(['Cliente', 'Trámite', 'Proceso', 'Descripción', 'Empleado', 'Fecha Inicio', 'Fecha Fin', 'tipo']);

// Datos de la tabla
$pdf->SetFont('Arial', '', 9);
foreach ($procesos as $proceso) {
    $pdf->Row([
        utf8_decode($proceso['cliente_nombre']),
        utf8_decode($proceso['tramite_nombre']),
        utf8_decode($proceso['proceso_nombre']),
        utf8_decode($proceso['proceso_descripcion']),
        utf8_decode($proceso['empleado_nombre']),
        $proceso['fecha_inicio'],
        $proceso['fecha_fin'],
        utf8_decode($proceso['tipo'])
    ]);
}

$pdf->Output('I', 'reporte_procesos.pdf');
