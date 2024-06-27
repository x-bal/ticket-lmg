<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportTransactionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
        $disc = $data->detail()->sum('total') * $data->discount / 100;

        return [
            $no++,
            Carbon::parse($data->created_at)->format('d/m/Y H:i:s'),
            $data->ticket_code,
            $data->ticket->name ?? '',
            $data->ticket->harga ?? '',
            $data->amount,
            $data->detail()->sum('total'),
            $data->detail()->sum('total') * $data->discount / 100,
            $data->detail()->sum('total') - $disc
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Tanggal',
            'Ticket Code',
            'Ticket',
            'Harga',
            'Amount',
            'Amount',
            'Discount',
            'Total',
        ];
    }
}
