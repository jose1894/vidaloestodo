<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */ 
    public function headings():array{
        return[
            'Id',
            'Brand',
            'SKU',
            'Codigo_int',
            'Name',
            'Model',
            'Has_variants',
            'Track_inventory',
            'Show_in_frontend',
            'Main_image',
            'Video_link',
            'Description',
            'Summary',
            'Specification',
            'Extra_descriptions',
            'Base_price',
            'Is_featured',
            'Is_special',
            'Iva',
            'Iva_id',
            'Meta_title',
            'Meta_description',
            'Meta_keywords',
            'Created_at',
            'Update_at',
            'Detele_at',
            'Is_plan',
            'Prime_price'
        ];
    } 
    public function collection()
    {
        return Product::all();
    }
}
