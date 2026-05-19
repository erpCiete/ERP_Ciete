<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'maintenance'])->group(function () {
    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('permission:admin.panel.ver')->group(function () {
        Route::get('/admin', AdminDashboardController::class)->name('admin.dashboard');
    });

    Route::middleware('permission:mantenimiento.gestionar')->group(function () {
        Route::post('/admin/maintenance', [MaintenanceController::class, 'toggle'])
            ->name('admin.maintenance.toggle');
    });

    Route::middleware('permission:soporte.gestionar')->group(function () {
        Route::get('/admin/soporte', [AdminSupportTicketController::class, 'index'])->name('admin.support.index');
        Route::get('/admin/soporte/{solicitudSoporte}', [AdminSupportTicketController::class, 'show'])->name('admin.support.show');
        Route::patch('/admin/soporte/{solicitudSoporte}/status', [AdminSupportTicketController::class, 'updateStatus'])->name('admin.support.status');
        Route::post('/admin/soporte/{solicitudSoporte}/reply', [AdminSupportTicketController::class, 'reply'])->name('admin.support.reply');
    });

    Route::middleware('permission:avisos.gestionar')->group(function () {
        Route::post('/admin/notices', [AdminNoticeController::class, 'store'])
            ->name('admin.notices.store');
        Route::put('/admin/notices/{homeNotice}', [AdminNoticeController::class, 'update'])
            ->name('admin.notices.update');
        Route::patch('/admin/notices/{homeNotice}/toggle', [AdminNoticeController::class, 'toggle'])
            ->name('admin.notices.toggle');
        Route::patch('/admin/notices/{homeNotice}/feature', [AdminNoticeController::class, 'feature'])
            ->name('admin.notices.feature');
        Route::post('/admin/notices/bulk', [AdminNoticeController::class, 'bulkUpdate'])
            ->name('admin.notices.bulk');
    });
    // ── Gestión de usuarios (accesible para admin y director) ────────────────
    Route::get('/admin/usuarios', [AdminUserController::class, 'index'])
        ->middleware('permission:usuarios.ver')
        ->name('admin.users.index');
    Route::get('/admin/usuarios/crear', [AdminUserController::class, 'create'])
        ->middleware('permission:usuarios.crear')
        ->name('admin.users.create');
    Route::post('/admin/usuarios', [AdminUserController::class, 'store'])
        ->middleware('permission:usuarios.crear')
        ->name('admin.users.store');
    Route::get('/admin/usuarios/{user}/editar', [AdminUserController::class, 'edit'])
        ->middleware('permission:usuarios.editar')
        ->name('admin.users.edit');
    Route::put('/admin/usuarios/{user}', [AdminUserController::class, 'update'])
        ->middleware('permission:usuarios.editar')
        ->name('admin.users.update');
    Route::post('/admin/usuarios/{user}/toggle', [AdminUserController::class, 'toggle'])
        ->middleware('permission:usuarios.editar')
        ->name('admin.users.toggle');

    Route::get('/admin/auditoria', [AdminUserController::class, 'audit'])
        ->middleware('audit.access')
        ->name('admin.audit');
    Route::get('/registro-actividad', [AuditLogController::class, 'index'])
        ->middleware('permission:auditoria.ver')
        ->name('registro.actividad.index');
    Route::get('/registro-actividad/exportar', [AuditLogController::class, 'exportar'])
        ->middleware('permission:auditoria.exportar')
        ->name('registro.actividad.exportar');
    Route::delete('/registro-actividad/limpiar', [AuditLogController::class, 'limpiar'])
        ->middleware('permission:auditoria.limpiar')
        ->name('registro.actividad.limpiar');
});
