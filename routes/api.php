<?php


use Illuminate\Support\Facades\Route;



Route::post('login', 'Api\LoginController@authenticate')->name('login');
Route::post('logout', 'Api\LoginController@logout')->name('logout');

/* Products */
Route::get('products', 'API\ProductController@index')->name('products');
Route::get('productsOffers', 'API\ProductController@productsOffers')->name('productsOffers');
Route::get('productsBestsellers', 'API\ProductController@productsBestsellers')->name('productsBestsellers');
Route::get('productsCategories', 'API\ProductController@productsCategories')->name('productsCategories');
Route::get('productsDetail/{slug}/', 'API\ProductController@productDetails')->name('productsDetail');
Route::get('productsRecents', 'API\ProductController@productsRecents')->name('productsRecents');
Route::get('search_bar_home', 'API\ProductController@search_bar_home')->name('search_bar_home');
Route::get('productsByCategory/{slug}/', 'API\ProductController@productsByCategory')->name('productsByCategory');

/* Categories */
Route::get('categories', 'API\CategoryController@index')->name('categories');
Route::get('categories_filter_menu', 'API\CategoryController@categories_filter_menu')->name('categoriesfiltermenu');

/** Banner */
Route::get('bannersMiddle/{key}/', 'API\BannerController@banners_middle')->name('bannersMiddle');
Route::get('bannersSlider/{key}/', 'API\BannerController@banners_sliders')->name('bannersSlider');
Route::get('allBanner/', 'API\BannerController@allBanner')->name('allBanner');


/** Cart */
Route::get('get_cart/{key}', 'API\CartController@getCart')->name('get-cart');
Route::get('search_cities', 'API\CartController@search_cities')->name('search_cities');
Route::post('add_to_cart/', 'API\CartController@addToCart')->name('add-to-cart');

Route::post('remove_cart_item/{id}', 'API\CartController@removeCartItem')->name('remove-cart-item');
Route::post('remove_cart_all', 'API\CartController@removeCartAll')->name('remove-cart-all');

/** General settings */
Route::get('general-settings/scope-sitename', 'API\GeneralSettingsController@scopeSitename')->name('scope-sitename');

/** WebServices */
Route::get('curl_products', 'API\WebServices@curl_products')->name('curl_products');
Route::get('curl_categories', 'API\WebServices@curl_categories')->name('curl_categories');
Route::get('categories_stelar', 'API\WebServices@categories_stelar')->name('categories_stelar');
Route::get('products_stelar', 'API\WebServices@products_stelar')->name('products_stelar');


Route::middleware('auth:sanctum')->group(function () {
       // Route::get('checkout/', 'API\CartController@checkout')->name('checkout');
        Route::post('post_shipping_user', 'API\CartController@post_shipping_user')->name('post_shipping_user');
        
        /**Order By */
        Route::post('/checkout/{type}', 'API\OrderController@confirmOrder')->name('checkout-to-payment');

        /** Deposit */
        Route::post('deposit_new', 'API\PaymentController@depositNew')->name('deposit_new');
        Route::get('deposit_reduce', 'API\PaymentController@reduceInventory')->name('deposit_reduce');

        //Checkout
        Route::get('checkout/', 'API\CartController@checkout')->name('checkout');
        
        //orders
        Route::get('orders/{type}', 'Api\OrderController@orders')->name('orders');
        Route::get('order/{order_number}', 'Api\OrderController@orderDetails')->name('order');

        // User
        Route::get('user/profile-setting', 'Api\UserController@profile')->name('profile-setting');
        Route::post('user/profile-setting', 'Api\UserController@submitProfile');
});
