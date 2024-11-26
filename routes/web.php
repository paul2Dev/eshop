<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {

    //dd(Storage::exists('products/images/01JDMHAMVBH2DMQGW7JBV57A4R.png'));
    //dd(Storage::delete('products/images/01JDMHAMVBH2DMQGW7JBV57A4R.png'));
    dump(Product::withTrashed()->find(11)->images()->count());

    Log::info('Test log message.');
    return view('welcome');
});
