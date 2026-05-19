<?php

use App\Http\Controllers\ActiveContextController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::post('/contexto/activo', [ActiveContextController::class, 'update'])->name('contexto.activo.update');
});
