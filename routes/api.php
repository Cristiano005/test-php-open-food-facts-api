<?php

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
