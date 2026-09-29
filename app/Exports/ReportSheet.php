<?php

namespace App\Exports;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * One worksheet of a report export.
 *
 *  - $rows      : a query (streamed in chunks) or a collection
 *  - $map       : turns one row into an array of cell values, in heading order
 *  - $text      : 1-based column numbers written as plain text, so long order numbers stay exact
 *                 and platform text (names, products) can never run as a formula
 *  - $money     : 1-based column numbers shown as 1,234.00
 *
 * Zeros stay zeros (strict null comparison), they are not written as empty cells.
 */
class ReportSheet extends DefaultValueBinder implements
    FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithStrictNullComparison
{
    private array $textLetters;

    public function __construct(
        private string $title,
        private array $headings,
        private EloquentBuilder|QueryBuilder|Collection $rows,
        private Closure $map,
        array $text = [],
        private array $money = [],
    ) {
        $this->textLetters = array_map([Coordinate::class, 'stringFromColumnIndex'], $text);
    }

    public function title(): string
    {
        return mb_substr($this->title, 0, 31); // Excel's sheet name limit
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function collection(): Collection
    {
        if ($this->rows instanceof Collection) {
            return $this->rows->map($this->map)->values();
        }

        $out = collect();
        $this->rows->chunk(1000, function ($chunk) use ($out) {
            foreach ($chunk as $row) {
                $out->push(($this->map)($row));
            }
        });

        return $out;
    }

    public function columnFormats(): array
    {
        $formats = [];
        foreach ($this->money as $column) {
            $formats[Coordinate::stringFromColumnIndex($column)] = NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
        }

        return $formats;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getRow() > 1 && in_array($cell->getColumn(), $this->textLetters, true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
