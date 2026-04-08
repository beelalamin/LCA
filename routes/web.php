<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
     return redirect('/admin');
 });

Route::get('/assets/{asset}/barcode', [App\Http\Controllers\AssetLabelController::class, 'barcode'])->name('asset.barcode');
Route::get('/assets/{asset}/qrcode', [App\Http\Controllers\AssetLabelController::class, 'qrcode'])->name('asset.qrcode');
Route::get('/assets/{asset}/label', [App\Http\Controllers\AssetLabelController::class, 'label'])->name('asset.label');
Route::get('/assets-bulk/labels', [App\Http\Controllers\AssetLabelController::class, 'bulkLabels'])->name('asset.bulk-labels');

Route::get('/set-locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('locale.switch');

