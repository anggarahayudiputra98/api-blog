<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

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

Route::get('/photo/blob', function (Request $request) {
    $photoUrl = $request->input('url');
    $filename = str_replace(env('APP_URL').'/','',$photoUrl);
    $path = env('PUBLIC_PATH').$filename;

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::controller(\App\Http\Controllers\Api\ArticleController::class)->group(function () {
    Route::get('/articles', 'articles');
    Route::get('/article', 'article');
    Route::get('/writter-articles', 'writterArticles');
});

Route::controller(\App\Http\Controllers\Api\TagController::class)->group(function () {
    Route::get('/tags', 'tags');
    Route::get('/tag/:id', 'tag');
});

Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'user']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    Route::controller(\App\Http\Controllers\Api\ArticleController::class)->group(function () {
        Route::post('/store-article', 'store');
        Route::post('/update-article', 'update');
        Route::post('/update-status', 'updateStatus');
        Route::delete('/delete-article', 'destroy');
    });
});
