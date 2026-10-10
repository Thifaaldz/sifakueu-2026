<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use App\Http\Controllers\LetterDocumentController;
use App\Http\Controllers\LetterVerificationController;

/* NOTE: Do Not Remove
/ Livewire asset handling if using sub folder in domain
*/

Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});
/*
/ END
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify/letter/{token}', [LetterVerificationController::class, 'show'])->name('letters.verify');
Route::get('/letters/{surat}/download', [LetterDocumentController::class, 'download'])->middleware('auth')->name('letters.download');
