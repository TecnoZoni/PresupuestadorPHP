<?php

namespace app\library\pdf;

use app\library\fpdf\FPDF;

class PresupuestoPDF extends FPDF
{
    private $marca     = [40, 55, 71];
    private $grisClaro = [240, 242, 245];
    private $grisLinea = [210, 214, 220];

    private $empresa  = [];
    private $logoPath = '';
    private $numero   = '';
    private $fecha    = '';

    private $cols = [
        'codigo'   => 28,
        'producto' => 82,
        'cantidad' => 20,
        'precio'   => 30,
        'subtotal' => 30,
    ];

    const VALIDEZ     = 'Este presupuesto tiene una validez de 15 días a partir de la fecha de emisión.';
    const CONDICIONES = 'Los precios pueden estar sujetos a modificaciones sin previo aviso. Los trabajos comienzan una vez aceptado el presupuesto por el cliente.';

    public function configurarEmpresa(array $empresa, $logoPath = '')
    {
        $this->empresa  = $empresa;
        $this->logoPath = $logoPath;
    }

    public function configurarDocumento($numero, $fecha)
    {
        $this->numero = $numero;
        $ts = strtotime((string) $fecha);
        $this->fecha = $ts ? date('d/m/Y', $ts) : $fecha;
    }

    private function enc($txt)
    {
        return mb_convert_encoding((string) $txt, 'ISO-8859-1', 'UTF-8');
    }

    private function moneda($valor)
    {
        return number_format((float) $valor, 2, ',', '.');
    }

    function Header()
    {
        $this->SetFillColor(...$this->marca);
        $this->Rect(0, 0, $this->w, 34, 'F');

        $offset = 0;
        if ($this->logoPath && is_file($this->logoPath)) {
            $this->Image($this->logoPath, 10, 6, 0, 22);
            $offset = 40;
        }

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 18);
        $this->SetXY(10 + $offset, 7);
        $this->Cell(120, 8, $this->enc($this->empresa['configuracion_nombre'] ?? ''), 0, 2, 'L');

        $this->SetFont('Arial', '', 8);
        $this->Cell(120, 4, $this->enc($this->empresa['configuracion_direccion'] ?? ''), 0, 2, 'L');

        $contacto = [];
        if (!empty($this->empresa['configuracion_telefono'])) $contacto[] = 'Tel: ' . $this->empresa['configuracion_telefono'];
        if (!empty($this->empresa['configuracion_email']))    $contacto[] = $this->empresa['configuracion_email'];
        if (!empty($this->empresa['configuracion_cuit']))     $contacto[] = 'CUIT: ' . $this->empresa['configuracion_cuit'];
        $this->Cell(120, 4, $this->enc(implode('   |   ', $contacto)), 0, 1, 'L');

        $this->SetXY(140, 7);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(60, 9, 'PRESUPUESTO', 0, 2, 'R');
        $this->SetFont('Arial', '', 10);
        $this->Cell(60, 5, $this->enc('N° ' . $this->numero), 0, 2, 'R');
        $this->Cell(60, 5, $this->enc('Fecha: ' . $this->fecha), 0, 1, 'R');

        $this->SetTextColor(0, 0, 0);
        $this->SetY(42);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetDrawColor(...$this->grisLinea);
        $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 10, $this->enc($this->empresa['configuracion_nombre'] ?? ''), 0, 0, 'L');
        $this->Cell(0, 10, $this->enc('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    public function bloqueCliente(array $cli)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(...$this->marca);
        $this->Cell(0, 6, 'CLIENTE', 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);

        $y = $this->GetY();
        $ancho = $this->w - $this->lMargin - $this->rMargin;
        $this->SetFillColor(...$this->grisClaro);
        $this->SetDrawColor(...$this->grisLinea);
        $this->Rect($this->lMargin, $y, $ancho, 22, 'DF');

        $this->SetXY($this->lMargin + 3, $y + 3);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 6, $this->enc(trim(($cli['cliente_nombre'] ?? '') . ' ' . ($cli['cliente_apellido'] ?? ''))), 0, 2, 'L');
        $this->SetX($this->lMargin + 3);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, $this->enc('Teléfono: ' . ($cli['cliente_telefono'] ?? '')), 0, 2, 'L');
        $this->SetX($this->lMargin + 3);
        $this->Cell(0, 5, $this->enc('Email: ' . ($cli['cliente_email'] ?? '')), 0, 1, 'L');

        $this->SetY($y + 22 + 6);
    }

    public function tablaEncabezado()
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(...$this->marca);
        $this->SetDrawColor(...$this->marca);
        $this->SetTextColor(255, 255, 255);
        $this->Cell($this->cols['codigo'], 8, $this->enc('Código'), 1, 0, 'L', true);
        $this->Cell($this->cols['producto'], 8, 'Producto', 1, 0, 'L', true);
        $this->Cell($this->cols['cantidad'], 8, 'Cant.', 1, 0, 'C', true);
        $this->Cell($this->cols['precio'], 8, $this->enc('Precio Unit.'), 1, 0, 'R', true);
        $this->Cell($this->cols['subtotal'], 8, 'Subtotal', 1, 1, 'R', true);
        $this->SetTextColor(0, 0, 0);
    }

    public function tablaFilas(array $items)
    {
        $this->SetFont('Arial', '', 9);
        $this->SetDrawColor(...$this->grisLinea);
        $h = 6;
        $i = 0;

        foreach ($items as $row) {
            $nombre  = $this->enc($row['producto_nombre']);
            $lineas  = max(1, $this->NbLines($this->cols['producto'], $nombre));
            $alto    = $lineas * $h;

            if ($this->GetY() + $alto > $this->PageBreakTrigger) {
                $this->AddPage();
                $this->tablaEncabezado();
                $this->SetFont('Arial', '', 9);
                $this->SetDrawColor(...$this->grisLinea);
            }

            $fill = ($i % 2 == 0);
            $this->SetFillColor(...($fill ? $this->grisClaro : [255, 255, 255]));

            $y = $this->GetY();

            $this->Cell($this->cols['codigo'], $alto, $this->enc($row['producto_codigo']), 'LR', 0, 'L', $fill);

            $xProd = $this->GetX();
            $this->MultiCell($this->cols['producto'], $h, $nombre, 'LR', 'L', $fill);
            $this->SetXY($xProd + $this->cols['producto'], $y);

            $this->Cell($this->cols['cantidad'], $alto, $row['detalle_presupuesto_cantidad'], 'LR', 0, 'C', $fill);
            $this->Cell($this->cols['precio'], $alto, $this->moneda($row['detalle_presupuesto_precio_unitario']), 'LR', 0, 'R', $fill);
            $this->Cell($this->cols['subtotal'], $alto, $this->moneda($row['detalle_presupuesto_subtotal']), 'LR', 1, 'R', $fill);

            $i++;
        }

        $this->Cell(array_sum($this->cols), 0, '', 'T', 1);
    }

    public function bloqueTotales($total)
    {
        $this->Ln(3);
        $etiqueta = $this->cols['codigo'] + $this->cols['producto'] + $this->cols['cantidad'] + $this->cols['precio'];
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(...$this->marca);
        $this->SetTextColor(255, 255, 255);
        $this->Cell($etiqueta, 9, 'TOTAL  ', 0, 0, 'R', true);
        $this->Cell($this->cols['subtotal'], 9, '$ ' . $this->moneda($total), 0, 1, 'R', true);
        $this->SetTextColor(0, 0, 0);
    }

    public function seccionesEstaticas()
    {
        $this->Ln(10);
        $this->bloqueTexto('Validez', self::VALIDEZ);
        $this->Ln(2);
        $this->bloqueTexto('Condiciones', self::CONDICIONES);
    }

    private function bloqueTexto($titulo, $cuerpo)
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(...$this->marca);
        $this->Cell(0, 5, $this->enc($titulo), 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->MultiCell(0, 5, $this->enc($cuerpo), 0, 'L');
    }

    public function NbLines($w, $txt)
    {
        $cw = $this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string) $txt);
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
            $l += isset($cw[$c]) ? $cw[$c] : 0;
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
