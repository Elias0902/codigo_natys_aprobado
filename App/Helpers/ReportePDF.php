<?php
namespace App\Natys\Helpers;
require_once __DIR__ . '/fpdf/fpdf.php';

class ReportePDF extends \FPDF
{
    protected $titulo;
    protected $subtitulo;
    protected $empresa = 'Natys';
    
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->SetAutoPageBreak(true, 25);
        $this->AliasNbPages();
    }
    
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }
    
    public function setSubtitulo($subtitulo)
    {
        $this->subtitulo = $subtitulo;
    }
    
    // Método para codificar texto a ISO-8859-1 y manejar caracteres especiales
    public function encodeText($text) {
        if (is_null($text) || $text === '') {
            return '';
        }
        
        // Convertir a string por si es un número u otro tipo
        $text = (string)$text;
        
        if (function_exists('mb_convert_encoding')) {
            // Primero normalizar los caracteres especiales
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Luego convertir a ISO-8859-1
            return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        } else {
            // Fallback básico si mbstring no está disponible
            return utf8_decode($text);
        }
    }
    
    public function Header()
    {
        // Limpiar buffer de salida al inicio del Header
        if (ob_get_length()) {
            ob_clean();
        }
        
        $this->SetFillColor(204, 29, 29);
        $this->Rect(0, 0, $this->GetPageWidth(), 25, 'F');
        
        $logoPath = __DIR__ . '/../../Assets/img/natys.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 5, 30);
        } else {
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(0, 10, $this->encodeText($this->empresa), 0, 1, 'L');
        }
        
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(8);
        $this->SetX(-60);
        $this->Cell(50, 4, $this->encodeText('Sistema de Reportes'), 0, 1, 'R');
        $this->SetX(-60);
        $this->Cell(50, 4, $this->encodeText('Natys Company'), 0, 1, 'R');
        $this->SetX(-60);
        $this->Cell(50, 4, $this->encodeText('Tel: (123) 456-7890'), 0, 1, 'R');
        
        $this->SetFillColor(245, 245, 245);
        $this->Rect(0, 25, $this->GetPageWidth(), 20, 'F');
        
        $this->SetY(28);
        
        if ($this->titulo) {
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor(51, 51, 51);
            $this->Cell(0, 8, $this->encodeText($this->titulo), 0, 1, 'C');
        }
        
        if ($this->subtitulo) {
            $this->SetFont('Arial', 'I', 11);
            $this->SetTextColor(102, 102, 102);
            $this->Cell(0, 6, $this->encodeText($this->subtitulo), 0, 1, 'C');
        }
        
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $fecha = date('d/m/Y H:i:s');
        $this->Cell(0, 4, $this->encodeText('Generado: ' . $fecha), 0, 1, 'R');
        
        $this->SetDrawColor(204, 29, 29);
        $this->SetLineWidth(0.8);
        $this->Line(10, $this->GetY() + 2, 200, $this->GetY() + 2);
        $this->Ln(8);
    }
    
    public function Footer()
    {
        $this->SetY(-20);
        
        $this->SetDrawColor(204, 29, 29);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(102, 102, 102);
        
        $this->Cell(0, 4, $this->encodeText('Natys - Sistema de Gestion'), 0, 1, 'C');
        $this->Cell(0, 4, $this->encodeText('Email: info@natys.com | Tel: (123) 456-7890'), 0, 1, 'C');
        $this->Cell(0, 4, $this->encodeText('www.natys.com'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(204, 29, 29);
        $this->Cell(0, 4, $this->encodeText('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
    
    /**
     * Crea una tabla con los encabezados y datos proporcionados
     * @param array $headers Encabezados de la tabla
     * @param array $data Datos de la tabla
     * @param array|null $widths Anchos personalizados para las columnas
     * @param array $aligns Alineación de las celdas (L, C, R)
     */
    public function crearTabla($headers, $data, $widths = null, $aligns = null)
    {
        $numColumns = count($headers);
        
        // Establecer anchos predeterminados si no se proporcionan
        if ($widths === null || count($widths) !== $numColumns) {
            $pageWidth = $this->GetPageWidth() - 20;
            $widths = array_fill(0, $numColumns, $pageWidth / $numColumns);
        }

        // Establecer alineaciones predeterminadas si no se proporcionan
        if ($aligns === null) {
            $aligns = array_fill(0, $numColumns, 'L'); // Por defecto alineación izquierda
        }

        // Configuración de estilos para el encabezado
        $this->SetFillColor(204, 29, 29); // Rojo Natys
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(0.3);
        $this->SetFont('Arial', 'B', 10);
        
        // Dibujar encabezados
        $totalWidth = 0;
        foreach ($headers as $i => $header) {
            $width = $widths[$i] ?? (($this->GetPageWidth() - 20) / $numColumns);
            $this->Cell($width, 8, $this->encodeText($header), 1, 0, 'C', true);
            $totalWidth += $width;
        }
        $this->Ln();

        // Configuración de estilos para las celdas de datos
        $this->SetTextColor(60, 60, 60);
        $this->SetFont('Arial', '', 9);
        $this->SetDrawColor(220, 220, 220);
        
        $fill = false;
        $rowHeight = 6; // Altura base de la fila
        
        foreach ($data as $rowIndex => $row) {
            // Calcular la altura necesaria para cada fila
            $maxLines = 1;
            $textLines = [];
            
            foreach ($row as $i => $cell) {
                $text = $this->encodeText($cell !== null ? $cell : '');
                $textLines[$i] = $this->wrapText($text, $widths[$i] - 2, $rowHeight);
                $maxLines = max($maxLines, count($textLines[$i]));
            }
            
            // Verificar si hay espacio suficiente en la página
            $currentY = $this->GetY();
            $pageHeight = $this->GetPageHeight();
            $neededHeight = ($rowHeight + 1) * $maxLines + 1;
            
            if ($currentY + $neededHeight > $pageHeight - 20) {
                $this->AddPage();
                $currentY = $this->GetY();
            }

            // Dibujar cada celda de la fila
            $y = $currentY;
            
            foreach ($headers as $i => $header) {
                $width = $widths[$i] ?? (($this->GetPageWidth() - 20) / $numColumns);
                $align = $aligns[$i] ?? 'L';
                $value = $row[$i] ?? '';
                
                // Establecer color de fondo alternado
                $this->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
                
                // Si hay múltiples líneas, usar MultiCell
                if (isset($textLines[$i]) && count($textLines[$i]) > 1) {
                    $this->SetXY($this->GetX(), $y);
                    $this->MultiCell($width, $rowHeight, implode("\n", $textLines[$i]), 1, $align, true);
                    $this->SetXY($this->GetX() + $width, $y);
                } else {
                    $this->SetXY($this->GetX(), $y);
                    $this->Cell($width, $neededHeight - 1, $this->encodeText($value), 1, 0, $align, true);
                }
            }
            
            $this->SetY($y + $neededHeight - 1);
            $fill = !$fill;
        }

        // Línea inferior de la tabla
        $this->SetDrawColor(180, 180, 180);
        $this->Cell($totalWidth, 0, '', 'T');
        $this->Ln(5);
    }

    /**
     * Envuelve el texto para que quepa en el ancho especificado
     * @param string $text Texto a envolver
     * @param float $maxWidth Ancho máximo en mm
     * @param float $lineHeight Altura de línea en mm
     * @return array Array de líneas de texto
     */
    private function wrapText($text, $maxWidth, $lineHeight)
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        
        foreach ($words as $word) {
            $testLine = $currentLine . ' ' . $word;
            $testWidth = $this->GetStringWidth($testLine);
            
            if ($testWidth > $maxWidth && $currentLine !== '') {
                $lines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine = ($currentLine === '') ? $word : $testLine;
            }
        }
        
        if ($currentLine !== '') {
            $lines[] = trim($currentLine);
        }
        
        return $lines;
    }

    /**
     * Agrega un resumen con los ítems proporcionados
     * @param array $items Array de íteles para el resumen
     */
    public function agregarResumen($items)
    {
        $this->SetFillColor(250, 250, 250);
        $this->SetDrawColor(204, 29, 29);
        $this->SetLineWidth(0.5);
        
        // Calcular la altura necesaria para el resumen
        $itemHeight = 7; // Altura por ítem en mm
        $padding = 8; // Espaciado interno
        $totalHeight = $padding + (count($items) * $itemHeight) + 5; // +5 para margen inferior
        
        // Dibujar el fondo del resumen
        $this->Rect(10, $this->GetY(), 190, $totalHeight, 'DF');
        
        // Título del resumen
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(204, 29, 29);
        $this->Cell(0, 8, $this->encodeText('RESUMEN EJECUTIVO'), 0, 1, 'C');
        $this->Ln(2);
        
        // Contenido del resumen
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(80, 80, 80);
        
        foreach ($items as $label => $value) {
            $this->SetX(15);
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(70, 6, $this->encodeText($label . ':'), 0, 0, 'L');
            $this->SetFont('Arial', 'B', 10);
            $this->SetTextColor(204, 29, 29);
            $this->Cell(0, 6, $this->encodeText($value), 0, 1, 'L');
            $this->SetTextColor(80, 80, 80);
        }
        
        $this->Ln(8);
    }
    
    public function agregarSeccion($titulo)
    {
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(204, 29, 29);
        $this->Cell(0, 10, $this->encodeText($titulo), 0, 1, 'L', true);
        $this->SetTextColor(100, 100, 100);
        $this->SetFont('Arial', 'I', 8);
        $this->Ln(3);
    }

    public function agregarParrafo($texto)
    {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(80, 80, 80);
        $this->MultiCell(0, 5, $this->encodeText($texto));
        $this->Ln(5);
    }

    public function agregarNota($texto)
    {
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->MultiCell(0, 4, $this->encodeText("Nota: " . $texto));
        $this->Ln(3);
    }
}