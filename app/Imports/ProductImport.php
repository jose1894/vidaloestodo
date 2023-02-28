<?php
namespace App\Imports;

use App\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        return $rows;
    }

    private function prepareCollection(Collection $collection)
    {
        return $collection->map(static function($row){
            return [
                'codigo' => $row[0]
            ];
        });
    }
}