<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerExport implements FromCollection, WithHeadings
{
    protected $customers;

    public function __construct(Collection $customers)
    {
        $this->customers = $customers;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Name',
            'Email',
            'Phone',
            'Address',
            'Total Orders',
            'Order List',
        ];
    }

    public function collection()
    {
        return $this->customers->map(function ($customer, $index) {
            return [
                $index + 1,
                $customer->name,
                $customer->email,
                $customer->phone,
                $customer->address,
                $customer->orders->count(),
                $customer->orders->pluck('order_number')->join(', '),
            ];
        });
    }
}
