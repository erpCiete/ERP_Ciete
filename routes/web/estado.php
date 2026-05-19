<?php

use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::get('/estado',  [StatusController::class, 'index'])->name('status');
});
