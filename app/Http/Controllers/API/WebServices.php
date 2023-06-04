<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;
use App\Product;
use App\ProductStock;
use App\ProductIva;
use Illuminate\Support\Facades\DB;

class WebServices extends Controller
{
    public function curl_products($variable)
    {
        $url = "https://us-west-2.aws.data.mongodb-api.com/app/webservice-paqno/endpoint/v2/data?arg1=" . urlencode($variable);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function curl_categories()
    {
        $url = "https://us-west-2.aws.data.mongodb-api.com/app/webservice-paqno/endpoint/v1/categ";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function categories_stelar()
    {
        $categories = $this->curl_categories();
        $datos = json_decode($categories, true);
        // Recorrer los datos y asignar valores al array de padres con sus hijos
        foreach ($datos as $dato) {
            $padre = $dato['PADRE'];
            $hijo = $dato['HIJO'];

            // Agregar el padre al array de padres con sus hijos si no existe
            if (!isset($padres_hijos[$padre])) {
                $padres_hijos[$padre] = array();
            }

            // Agregar el hijo al array de hijos correspondiente al padre
            $padres_hijos[$padre][] = $hijo;
        }

        $this->traverseArray($padres_hijos);
        return response()->json($padres_hijos);
    }

    public function traverseArray($padres_hijos)
    {
        foreach ($padres_hijos as $padre => $hijos) {
            $category = new Category();
            $category->name             = ucwords(strtolower($padre));
            $category->slug             = Str::slug($padre, '-');
            $category->parent_id        = NULL;
            $category->icon             = "<i class='fas fa-heartbeat'></i>";
            $category->meta_title       = NULL;
            $category->meta_description = NULL;
            $category->meta_keywords    = NULL;
            $category->image            = NULL;
            $category->is_top           = 0;
            $category->is_special       = 0;
            $category->in_filter_menu   = 1;
            $category->position   = 0;
            $category->save();
            $id = $category->id;

            foreach ($hijos as $hijo) {
                $category = new Category();
                $category->name             = ucwords(strtolower($hijo));
                $category->slug             = Str::slug($hijo, '-');
                $category->parent_id        = $id;
                $category->icon             = "<i class='fas fa-heartbeat'></i>";
                $category->meta_title       = NULL;
                $category->meta_description = NULL;
                $category->meta_keywords    = NULL;
                $category->image            = NULL;
                $category->is_top           = 0;
                $category->is_special       = 0;
                $category->in_filter_menu   = 0;
                $category->position   = 0;
                $category->save();
            }
        }
    }

    public function products_stelar()
    {
        $categories = Category::whereNull('parent_id')->get();

         foreach ($categories as $category) {
         $products = $this->curl_products(strtoupper($category['name']));
        //$products = $this->curl_products(strtoupper('Aseo Y Uso'));
        $datos = json_decode($products, true);
        $categories = Category::all()->toArray();
        $productIva = ProductIva::all()->toArray();

        if ($datos) {
            foreach ($datos as $dato) {
                $departamento = ucwords(strtolower($dato['Departamento']));
                $grupo = ucwords(strtolower($dato['Grupo']));

                $indexDepartment = array_search($departamento, array_column($categories, 'name'));
                $indexGroup = array_search($grupo, array_column($categories, 'name'));
                $indexIva = array_search($dato['Iva'], array_column($productIva, 'productIva'));

                $padre = $categories[$indexDepartment]['id'];
                $hijo = $categories[$indexGroup]['id'];

                $product = new Product();
                $product->brand_id          = NULL;
                $product->sku               = $dato['SKU'];
                $product->name              = ucwords(strtolower($dato['Nombre']));
                $product->slug              = Str::slug($dato['Nombre'], '-');
                $product->description       = $dato['Descripcion'];
                $product->base_price        = $dato['Precio'];
                $product->prime_price       = $dato['Precio'];
                $product->iva               = $dato['Iva'];
                $product->iva_id            = $indexIva;
                $product->save();
                
                $product->categories()->attach([$categories[$indexDepartment]['id'], $categories[$indexGroup]['id']]);
                $product->tags()->attach([1]);
                
                $productStock = new ProductStock();
                $productStock->product_id = $product->id;
                $productStock->sku = $dato['SKU'];
                $productStock->quantity = $dato['Stock'];
                $productStock->save();

               // dd("Padre: " . $padre . " Hijo: " . $hijo. " Producto: ".$product->id);
               //usleep(100000);
            }
        }

        // usleep(100000);
         }
        // return response()->json(['categories' => $categories, 'products' => $products]);
    }
}
