<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Product;
use App\ProductIva;
use Carbon\Carbon;

class ProductIvaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $iva = new ProductIva();
        $iva->name = 'Iva 16%';
        $iva->percentage = 16;
        $iva->status = 1;
        $iva->created_at = Carbon::now();
        $iva->save();

        $products = Product::where('iva',1)->get();
        foreach($products as $item){
            $item->iva_id = $iva->id;
            $item->save();
        }

        
    }
}
