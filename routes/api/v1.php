<?php

use App\Http\Controllers\Api\Subscription\AppleSubscriptionController;
use App\Http\Controllers\Api\Users\UsersController;
use App\Http\Controllers\Api\UserTokenApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['verify.signature'])->group(function () {
    Route::post('token', [UserTokenApiController::class, 'store']);
});

Route::middleware(['auth:api', 'verify.signature'])->group(function () {

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UsersController::class, 'userDetails'])->name('user-details');
        Route::get('/lookups', [UsersController::class, 'lookups'])->name('lookups');
        Route::put('/update', [UsersController::class, 'update'])->name('update');
    });

    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::post('/add', [AppleSubscriptionController::class, 'addSubscription'])->name('add');
    });

});
