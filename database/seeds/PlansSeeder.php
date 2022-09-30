<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Product;
use App\PlanDetails;
use App\PlanUsers;
use App\ProductStock;
use Carbon\Carbon;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $i = 1;
        $productOld = Product::select('main_image')->orderBy('created_at', 'desc')->first();
        while($i < 3){
            //creo el producto
            $plan = new Product();
            $plan->sku = 'plan_' . $i;
            $plan->name = "Plan " . $i;
            $plan->has_variants      = 0;
            $plan->track_inventory   = 0;
            $plan->show_in_frontend  = 0;
            $plan->main_image        = $productOld->main_image;
            $plan->base_price        = 29.99;
            $plan->is_plan           = 1;
            $plan->save();

            //creo el stock
            $stock = new ProductStock();
            $stock->product_id = $plan->id;
            $stock->sku        = $plan->sku;
            $stock->quantity   = 1000000000;
            $stock->save();

            //creo el plan_details
            $plan_detail = new PlanDetails();          
            $plan_detail->name = $plan->name;
            $plan_detail->meses = $i == 1 ? 6 : 12;
            $plan_detail->description = $plan->name . ' de ' . $plan_detail->meses . ' meses';
            $plan_detail->product_id = $plan->id;
            $plan_detail->created_at = Carbon::now();
            $plan_detail->save();

            $i++;
        }
    }
}
