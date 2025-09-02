<?php

use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class);