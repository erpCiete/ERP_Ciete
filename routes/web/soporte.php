<?php

use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::get('/soporte', [SupportController::class, 'index'])->name('support');
    Route::post('/soporte', [SupportController::class, 'send'])->name('support.send');
    Route::get('/soporte/{solicitudSoporte}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/soporte/{solicitudSoporte}/reply', [SupportController::class, 'reply'])->name('support.reply');
});
