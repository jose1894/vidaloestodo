<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;
use App\ShippingMethod;
use Carbon\Carbon;

class ShippingMethodPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sm = new ShippingMethod();
        $sm->name = 'ShippingMethod to plans';
        $sm->shipping_time	= 1;
        $sm->created_at	= Carbon::now();
        $sm->is_plan	= 1;
        $sm->save();
    }
}
