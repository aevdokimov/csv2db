<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::controller(ProductController::class)->group(function () {
    Route::get('/', 'getUploadCsvPage');
    Route::post('/', 'postUploadCsv');
    
    Route::get('/products', 'getProducts');
});

Route::get('test', function () {
    var_dump(Storage::path('csvses/products.csv'));
    var_dump(storage_path('csvses/products.csv'));
});