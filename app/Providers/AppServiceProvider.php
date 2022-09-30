<?php

namespace App\Providers;

use App\GeneralSetting;
use App\Language;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Auth;
 use Carbon\Carbon;
 use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $activeTemplate = activeTemplate();

        $viewShare['general'] = GeneralSetting::first();
        $viewShare['activeTemplate'] = $activeTemplate;
        $viewShare['activeTemplateTrue'] = activeTemplate(true);
        $viewShare['language'] = Language::all();

        // $viewShare['categories'] = \App\Category::with(['allSubcategories','products'=> function($q){
        //     return $q->whereHas('categories'); 
        // }, 'products.reviews', 'products.offer', 'products.offer.activeOffer'])->where('parent_id', null)->get();

        $categories = \App\Category::with(
            [
                'allSubcategories',
                'products'=> function($q){
                    return $q->whereHas('categories'); 
                }, 
                'products.reviews', 
                'products.offer', 
                'products.offer.activeOffer',
                'products.stocks'=> function($q){
                    $q->where('quantity','>',0);
                },
            ]
        )->where('parent_id', null)->get();

        $categoryProductFilter = [];
        foreach($categories as $key => $item){
            foreach($item->products as $product){
                if((!is_null($product->stocks)) && (count($product->stocks) > 0)){
                    $categoryProductFilter[$key] = $item;
                }
            }
        }

        $viewShare['categories'] = $categoryProductFilter;

        view()->share($viewShare);

        view()->composer('templates.basic.partials.header', function ($view) {
            $categories = \App\Category::with(
                [
                    'allSubcategories',
                    'products'=> function($q){
                        return $q->whereHas('categories'); 
                    }, 
                    'products.reviews', 
                    'products.offer', 
                    'products.offer.activeOffer',
                    'products.stocks'=> function($q){
                        $q->where('quantity','>',0);
                    },
                ]
            )->where('parent_id', null)->get();
    
            $categoryProductFilter = [];
            foreach($categories as $key => $item){
                foreach($item->products as $product){
                    if((!is_null($product->stocks)) && (count($product->stocks) > 0)){
                        $categoryProductFilter[$key] = $item;
                    }
                }
            }
            $view->with([
                'categories_with_products_in_stock'           => $categoryProductFilter,
            ]);
        });

        view()->composer('admin.partials.sidenav', function ($view) {
            $view->with([
                'banned_users_count'           => \App\User::banned()->count(),
                'email_unverified_users_count' => \App\User::emailUnverified()->count(),
                'sms_unverified_users_count'   => \App\User::smsUnverified()->count(),
                'pending_ticket_count'         => \App\SupportTicket::whereIN('status', [0,2])->count(),
                'pending_deposits_count'       => \App\Deposit::pending()->count(),

                'pending_orders_count'          => \App\Order::where('status', 0)->where('payment_status',  '!=' ,0)->count(),
                'processing_orders_count'       => \App\Order::where('status', 1)->where('payment_status','!=', 0)->count(),
                'dispatched_orders_count'       => \App\Order::where('status', 2)->where('payment_status','!=', 0)->count(),
                'userlog' => \App\Admin::where('id', Auth::guard('admin')->user()->id)->with('roles.modules')->first(),
            ]);
        });

        view()->composer('templates.basic.user.partials.sidebar', function ($view) {
            $view->with([
                'pending_ticket_count'         => \App\SupportTicket::where('status', 1)->where('user_id', auth()->user()->id)->count(),
            ]);
        });

        $pages  = $seo = \App\Frontend::where('data_keys', 'pages.element')->get();
        view()->composer([$activeTemplate.'partials.header', $activeTemplate.'partials.footer'], function ($view) use($pages){
            $view->with([
                'pages' => $pages,
            ]);
        });


        /*
         * use Illuminate\Support\Collection;
         * use Illuminate\Pagination\LengthAwarePaginator;
         *
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */
        Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

        Carbon::setUTF8(true);
        Carbon::setLocale('es');
        setlocale(LC_ALL, 'es_MX', 'es', 'ES', 'es_MX.utf8');
        


    }
}
