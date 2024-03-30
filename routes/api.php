<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', function () {
    return response()->json([
        "message" => "Forbidden access"
    ], 403);
})->name('login');

Route::post('/v1/auth/register', [AuthController::class, 'register']);
Route::post('/v1/auth/login', [AuthController::class, 'login']);
Route::post('/v1/auth/login', [AuthController::class, 'login']);
Route::get('/v1/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/v1/posts', [PostController::class, 'index'])->middleware('auth:sanctum');
Route::post('/v1/posts', [PostController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/v1/posts/{id}', [PostController::class, 'destroy'])->middleware('auth:sanctum');
