<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
     return redirect('/admin');
 });

Route::get('/assets/{asset}/barcode', [App\Http\Controllers\AssetLabelController::class, 'barcode'])->name('asset.barcode');
Route::get('/assets/{asset}/qrcode', [App\Http\Controllers\AssetLabelController::class, 'qrcode'])->name('asset.qrcode');

