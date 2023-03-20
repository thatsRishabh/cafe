<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StockImport implements ToCollection, WithHeadingRow,WithChunkReading, ShouldQueue, WithStartRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }
    public function headingRow(): int
    {
        return 1;
    }

    public function startRow(): int 
    {
         return 2;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
