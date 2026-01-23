<?php

use App\Http\Controllers\Api\UserTokenApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['verify.signature'])->group(function () {
    Route::post('token', [UserTokenApiController::class, 'store']);
});
Route::prefix('v1')
    ->middleware('api')
    ->group(base_path('routes/api/v1.php'));

Route::prefix('v2')
    ->middleware('api')
    ->group(base_path('routes/api/v2.php'));
