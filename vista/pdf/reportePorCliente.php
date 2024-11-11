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

// Extender la clase FPDF para manejar celdas dinámicas
class PDF extends FPDF
{
    // Definir anchos de columnas
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

// Verificar si el cliente fue enviado
if (!isset($_GET['cliente_id']) || empty($_GET['cliente_id']) || !filter_var($_GET['cliente_id'], FILTER_VALIDATE_INT)) {
    die("Error: Cliente no válido.");
}

$cliente_id = $_GET['cliente_id'];

// Consulta para obtener los procesos
$stmt = $conn->prepare("
    SELECT 
        cl.id AS cliente_id,
        CONCAT(cl.nombre, ' ', cl.apellido_paterno, ' ', cl.apellido_materno) AS cliente_nombre_completo,
        tr.nombre AS tramite_nombre,
        p.nombre AS proceso_nombre,
        p.descripcion AS proceso_descripcion,
        p.punto_origen,
        p.punto_destino,
        p.tipo_carga,
        p.peso_mercancia,
        ep.nombre AS estado_proceso,
        dt.fecha_inicio,
        dt.fecha_fin
    FROM clientes cl
    JOIN tramites tr ON cl.id = tr.cliente_id
    JOIN procesos p ON tr.id = p.tramite_id
    JOIN detalle_tramite dt ON p.id = dt.proceso_id
    JOIN estado_proceso ep ON dt.estado_id = ep.id
    WHERE cl.id = :cliente_id
    ORDER BY dt.fecha_inicio DESC
");
$stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
$stmt->execute();
$procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Validar si hay resultados
if (count($procesos) === 0) {
    die("No se encontraron procesos para el cliente seleccionado.");
}

// Crear el PDF
$pdf = new PDF('L', 'mm', 'A4'); // Horizontal (L), Milímetros, A4
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Encabezado
$pdf->Cell(0, 10, utf8_decode('Reporte de Procesos - Cliente: ' . $procesos[0]['cliente_nombre_completo']), 0, 1, 'C');
$pdf->Ln(10);

// Tabla de contenido
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetWidths([40, 40, 50, 30, 30, 30, 40]);
$pdf->SetAligns(['L', 'L', 'L', 'L', 'C', 'C', 'L']);
$pdf->Row(['Tramite', 'Proceso', 'Descripcion', 'Origen', 'Destino', 'Estado']);

// Contenido de la tabla
$pdf->SetFont('Arial', '', 10);
foreach ($procesos as $proceso) {
    $pdf->Row([
        utf8_decode($proceso['tramite_nombre']),
        utf8_decode($proceso['proceso_nombre']),
        utf8_decode($proceso['proceso_descripcion']),
        utf8_decode($proceso['punto_origen']),
        utf8_decode($proceso['punto_destino']),
        utf8_decode($proceso['estado_proceso']),
      
    ]);
}

// Salida del PDF
$pdf->Output('I', 'reporte_procesos_cliente.pdf');
?>
