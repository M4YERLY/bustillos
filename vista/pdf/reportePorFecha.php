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

// Validar parámetros de fechas
if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
    die("Error: No se seleccionaron fechas.");
}

$fecha_inicio = $_GET['fecha_inicio'];
$fecha_fin = $_GET['fecha_fin'];

// Consulta para obtener los datos
$stmt = $conn->prepare("
    SELECT 
        p.nombre AS proceso_nombre,
        dt.fecha_inicio,
        dt.fecha_fin,
        DATEDIFF(dt.fecha_fin, CURDATE()) AS dias_restantes,
        CONCAT(cl.nombre, ' ', cl.apellido_paterno, ' ', cl.apellido_materno) AS cliente_nombre_completo,
        t.nombre AS tramite_nombre,
        e.nombre AS empleado_responsable
    FROM detalle_tramite dt
    JOIN procesos p ON dt.proceso_id = p.id
    JOIN tramites t ON dt.tramite_id = t.id
    JOIN clientes cl ON t.cliente_id = cl.id
    JOIN empleados e ON dt.empleado_id = e.id
    WHERE dt.fecha_fin BETWEEN :fecha_inicio AND :fecha_fin
    ORDER BY dt.fecha_fin ASC
");
$stmt->bindParam(':fecha_inicio', $fecha_inicio);
$stmt->bindParam(':fecha_fin', $fecha_fin);
$stmt->execute();
$procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear PDF
$pdf = new PDF('L', 'mm', 'A4');
$pdf->SetLeftMargin(10);
$pdf->SetRightMargin(10);
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Encabezado
$pdf->Cell(0, 10, utf8_decode("Reporte de Procesos por Fechas"), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode("Rango de fechas: $fecha_inicio a $fecha_fin"), 0, 1, 'C');
$pdf->Ln(10);

// Tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetWidths([50, 40, 40, 30, 60, 60]);
$pdf->SetAligns(['L', 'C', 'C', 'C', 'L', 'L']);
$pdf->Row(['Proceso', 'Fecha Inicio', 'Fecha Fin', 'Días Restantes', 'Cliente', 'Empleado']); // Encabezado

$pdf->SetFont('Arial', '', 9);
foreach ($procesos as $proceso) {
    $pdf->Row([
        utf8_decode($proceso['proceso_nombre']),
        $proceso['fecha_inicio'],
        $proceso['fecha_fin'],
        $proceso['dias_restantes'],
        utf8_decode($proceso['cliente_nombre_completo']),
        utf8_decode($proceso['empleado_responsable']),
    ]);
}

// Salida del PDF
$pdf->Output('I', 'reporte_procesos_fechas.pdf');
