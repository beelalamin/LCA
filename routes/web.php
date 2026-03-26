<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
     return redirect('/admin');
 });

Route::get('/assets/{asset}/label', [App\Http\Controllers\AssetLabelController::class, 'show'])->name('asset.label');

