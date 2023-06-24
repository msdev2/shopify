<?php

use Illuminate\Routing\Route;

use Msdev2\Shopify\Http\Controllers\ShopifyController;

Route::fallback([ShopifyController::class , 'fallback'])->middleware('msdev2.shopify.installed');
Route::post("install",[ShopifyController::class,'install'])->name("msdev2.install");
Route::get("install",function(){
    return view('msdev2::install');
});
Route::get('auth/callback', [ShopifyController::class,'generateToken'])->name("msdev2.callback");