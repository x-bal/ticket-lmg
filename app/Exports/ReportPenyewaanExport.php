<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportPenyewaanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $data;
    function __construct($data)
    {
        $this->data = $data;
    }
    public function collection()
    {
        return $this->data;
    }

    public function map($data): array
    {
        $no = 1;

        return [
            $no++,
            Carbon::parse($data->created_at)->format('d/m/Y H:i:s'),
            $data->sewa->name ?? '',
            $data->metode,
            $data->sewa->harga ?? '',
            $data->qty,
            $data->jumlah,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Tanggal',
            'Sewa',
            'Metode',
            'Harga',
            'QTY',
            'Total',
        ];
    }
}
