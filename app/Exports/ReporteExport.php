<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Genérica: recibe la misma forma de datos que ya usan la pantalla y el PDF
 * (titulo, periodoLabel, generadoEn, columnas, filas, resumen, chart) y arma
 * un .xlsx con el mismo lenguaje visual de marca — incluyendo una gráfica
 * nativa de Excel cuando el reporte trae "chart" (todos los días, ceros
 * incluidos). Un reporte nuevo no necesita tocar esta clase.
 */
class ReporteExport
{
    private const BRAND = 'C81E3A';

    public function __construct(private array $datos)
    {
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $tituloHoja = str($this->datos['titulo'])->limit(31, '')->value();
        $sheet->setTitle($tituloHoja);

        $numCols = max(count($this->datos['columnas']), 2);
        $ultimaCol = Coordinate::stringFromColumnIndex($numCols);

        $fila = $this->escribirEncabezado($sheet, $ultimaCol);
        $fila = $this->escribirResumen($sheet, $fila);

        if (! empty($this->datos['chart'])) {
            $fila = $this->escribirDatosGraficaYChart($spreadsheet, $sheet, $fila, $numCols);
        }

        $this->escribirTabla($sheet, $fila, $ultimaCol);

        foreach (range('A', $ultimaCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function escribirEncabezado($sheet, string $ultimaCol): int
    {
        $sheet->mergeCells("A1:{$ultimaCol}1");
        $sheet->setCellValue('A1', 'CLUB DE FANTASÍAS · Panel de Administrador');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BRAND);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->mergeCells("A2:{$ultimaCol}2");
        $sheet->setCellValue('A2', $this->datos['titulo']);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

        $fila = 3;
        if (! empty($this->datos['periodoLabel'])) {
            $sheet->setCellValue("A{$fila}", 'Periodo');
            $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->getColor()->setRGB('8A8481');
            $sheet->setCellValue("B{$fila}", $this->datos['periodoLabel']);
            $fila++;
        }
        $sheet->setCellValue("A{$fila}", 'Generado');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->getColor()->setRGB('8A8481');
        $sheet->setCellValue("B{$fila}", $this->datos['generadoEn']);

        return $fila + 2;
    }

    private function escribirResumen($sheet, int $fila): int
    {
        if (empty($this->datos['resumen'])) {
            return $fila;
        }

        $sheet->setCellValue("A{$fila}", 'RESUMEN');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->getColor()->setRGB(self::BRAND);
        $fila++;
        foreach ($this->datos['resumen'] as $r) {
            $sheet->setCellValue("A{$fila}", $r['label']);
            $sheet->setCellValue("B{$fila}", $r['valor']);
            $sheet->getStyle("B{$fila}")->getFont()->setBold(true);
            $fila++;
        }

        return $fila + 1;
    }

    /** Escribe la tabla chica (Fecha/Total, todos los días) que respalda la gráfica, y dibuja la gráfica nativa junto a ella. */
    private function escribirDatosGraficaYChart(Spreadsheet $spreadsheet, $sheet, int $fila, int $numCols): int
    {
        $sheet->setCellValue("A{$fila}", 'DATOS DE LA GRÁFICA');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->getColor()->setRGB(self::BRAND);
        $fila++;

        $filaEncabezado = $fila;
        $sheet->setCellValue("A{$fila}", 'Fecha');
        $sheet->setCellValue("B{$fila}", 'Total');
        $sheet->getStyle("A{$fila}:B{$fila}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$fila}:B{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BRAND);
        $fila++;

        $primeraFilaDatos = $fila;
        foreach ($this->datos['chart']['labels'] as $i => $etiqueta) {
            $sheet->setCellValue("A{$fila}", $etiqueta);
            $sheet->setCellValue("B{$fila}", $this->datos['chart']['data'][$i]);
            $fila++;
        }
        $ultimaFilaDatos = $fila - 1;

        // --- Gráfica nativa de Excel (de barras) ---
        $hoja = $sheet->getTitle();
        $cantidad = $ultimaFilaDatos - $primeraFilaDatos + 1;

        $categorias = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'{$hoja}'!\$A\${$primeraFilaDatos}:\$A\${$ultimaFilaDatos}",
            null,
            $cantidad
        );
        $valores = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$hoja}'!\$B\${$primeraFilaDatos}:\$B\${$ultimaFilaDatos}",
            null,
            $cantidad
        );

        $serie = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, 0),
            [],
            [$categorias],
            [$valores]
        );
        $serie->setPlotDirection(DataSeries::DIRECTION_COL);

        $plotArea = new PlotArea(null, [$serie]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $titulo = new Title($this->datos['titulo']);

        $chart = new Chart('grafica_' . uniqid(), $titulo, $legend, $plotArea);

        // La ancla junto a los datos, para que no se encime con el resto.
        $colInicio = Coordinate::stringFromColumnIndex($numCols + 2);
        $colFin = Coordinate::stringFromColumnIndex($numCols + 10);
        $chart->setTopLeftPosition("{$colInicio}" . ($filaEncabezado - 1));
        $chart->setBottomRightPosition("{$colFin}" . ($filaEncabezado + 18));

        $sheet->addChart($chart);

        return $fila + 1;
    }

    private function escribirTabla($sheet, int $fila, string $ultimaCol): void
    {
        $filaEncabezado = $fila;
        foreach ($this->datos['columnas'] as $i => $col) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $fila, $col);
        }
        $sheet->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BRAND);
        $fila++;

        foreach ($this->datos['filas'] as $indiceFila => $filaDatos) {
            foreach ($filaDatos as $i => $valor) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $fila, $valor);
            }
            if ($indiceFila % 2 === 1) {
                $sheet->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAF8F7');
            }
            $fila++;
        }

        if (! empty($this->datos['filas'])) {
            $sheet->getStyle("A{$filaEncabezado}:{$ultimaCol}" . ($fila - 1))
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('ECE9E7');
        }
    }
}