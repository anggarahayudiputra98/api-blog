<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    Route::controller(\App\Http\Controllers\Api\ArticleController::class)->group(function () {
        Route::get('/articles', 'articles');
        Route::get('/article/:id', 'article');
        Route::post('/store-article', 'store');
        Route::post('/update-article', 'update');
        Route::delete('/delete-article/:id', 'destroy');
    });

    Route::controller(\App\Http\Controllers\Api\TagController::class)->group(function () {
        Route::get('/tags', 'tags');
        Route::get('/tag/:id', 'tag');
        Route::post('/store-tag', 'store');
        Route::post('/update-tag', 'update');
        Route::delete('/delete-tag/:id', 'destroy');
    });
});
