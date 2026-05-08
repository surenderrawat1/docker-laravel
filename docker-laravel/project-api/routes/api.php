<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Product;

Route::get('/products', function () {
    return Product::select('id', 'name', 'price')->limit(5)->get();
});

Route::get('/plain-ok', function () {
    return 'OK';
});
Route::get('/json-ok', function () {
    return response()->json([
        'ok' => true
    ]);
});

use Illuminate\Support\Facades\Cache;

Route::get('/redis', function () {

    $data = Cache::remember('products', 60, function () {
        return Product::select('id', 'name', 'price')->limit(5)->get();
    });

    return response()->json($data);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
