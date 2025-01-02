<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;
use App\Http\Middleware\CheckApiKey;


Route::middleware(CheckApiKey::class)->group(function () {
    Route::get('/stores/delivering/{postcode}', [StoreController::class, 'storesDelivering']);
    Route::get('/stores/near/{postcode}', [StoreController::class, 'storesNear']);

    Route::apiResource('stores', StoreController::class)->parameters([
        'stores' => 'store_uuid'
    ]);
});

Route::get('/', function () {
    return response()->json(['message' => 'Hello World']);
});

