<?php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $isConnectionOk = true;

    try {
        DB::connection()->getPdo();
        DB::table('products')->select('code')->get();
    } catch (PDOException) {
        $isConnectionOk = false;
    }

    return response()->json([
        'database_connection' => $isConnectionOk ? 'ok' : 'failed',
        'online_time' => now()->subSeconds(floatval(file_get_contents('/proc/uptime')))->diffForHumans(),
        'memory_use' => (memory_get_usage(true) / 1024) / 1024 . "MB",
        'last_cron_updated' => Cache::get('last-cron-updated', 'No updates'),
    ], 200);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/product/{product:code}', [ProductController::class, 'show']);
Route::put('/products/{product:code}', [ProductController::class, 'update']);
Route::delete('/products/{product:code}', [ProductController::class, 'destroy']);