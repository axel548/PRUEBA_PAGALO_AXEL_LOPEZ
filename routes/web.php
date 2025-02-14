<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    require __DIR__.'/api.php';
});


Route::get('/', function () {
    return view('welcome');
});
