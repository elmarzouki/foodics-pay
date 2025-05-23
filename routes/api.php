<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;


Route::prefix('v1')->group(function () {
    Route::get('/health_check', fn () => response()->json(['status' => 'ok']));
    Route::post('/webhook/{bank}', [WebhookController::class, 'handle']);
});