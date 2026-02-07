<?php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {

    return response()->json([
        'status' => 200,
        'online_time' => null,
        'memory_use' => memory_get_usage(true),
        'last_datetime_executed_cron' => now(),
    ], 200);

});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/product/{product:code}', [ProductController::class, 'show']);
Route::put('/products/{product:code}', [ProductController::class, 'update']);