<?php

use App\Http\Controllers\ApiGatewayController;
use Illuminate\Support\Facades\Route;

// API Gateway routes
Route::prefix('gateway')->middleware('api')->group(function () {
    Route::get('health', [ApiGatewayController::class, 'health']);
    Route::any('{module}/{path?}', [ApiGatewayController::class, 'route'])
        ->where('path', '.*');
});

// Direct module routes (current approach - modules expose their own routes)
Route::middleware('api')->group(function () {
    require __DIR__.'/modules.php';
});

