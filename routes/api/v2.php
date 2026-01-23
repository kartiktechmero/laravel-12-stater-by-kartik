<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'verify.signature'])->group(function () {
    Route::get('/test', function () {
        return 'test';
    });
});
