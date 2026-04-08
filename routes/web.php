<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', function () {
    return Inertia::render('Welcome');
})->middleware('auth')->name('index');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('permission:proyectos.ver')->group(function () {
        Route::get('/proyectos', function () {
            return Inertia::render('Proyectos/Index');
        })->name('proyectos.index');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');
    });

    Route::middleware('role:control_cierre,admin')->group(function () {
        Route::get('/cierre', function () {
            return Inertia::render('Cierre/Dashboard');
        })->name('cierre.dashboard');
    });
});

require __DIR__.'/auth.php';
