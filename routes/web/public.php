<?php

use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Support\HomeNoticeCatalog;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::get('/', function () {
        $homeNotices = AdminNoticeController::loadActive();

        return Inertia::render('Welcome', [
            'homeNotices' => $homeNotices,
            'featuredNotice' => HomeNoticeCatalog::featuredActive(),
        ]);
    })->name('index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/perfil/preferencias', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
    // ── Páginas generales ─────────────────────────────────────────────────────
    Route::get('/ayuda',   function () {
        return Inertia::render('Help');
    })->name('help');
});
