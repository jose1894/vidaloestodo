<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Product;


class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = 'imgs/';
        $files = glob(public_path($path) . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        
        $products = Product::get();
        
        foreach($products as $item){
            $image = 'imgs/'.$item->sku.'.jpg';

            if (realpath($image)){
                $item->main_image = copyImageForSeeder($image,'public/'.imagePath()['product']['path'],imagePath()['product']['size'],@$image,imagePath()['product']['thumb']);
                $item->save();
            }
        }
    }
}
