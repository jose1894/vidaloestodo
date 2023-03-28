<?php


use Illuminate\Support\Facades\Route;



Route::post('login', 'Api\LoginController@authenticate')->name('login');

Route::get('prueba',function() {
    
    return "Ejecutado 'php artisan migrate'";
});