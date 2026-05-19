<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'maintenance'])->group(function () {
    if (app()->environment(['local', 'testing'])) {
        Route::get('/_preview/error/{status}', function (int $status) {
            abort_unless(in_array($status, [401, 403, 404, 419, 500, 503], true), 404);
            return Inertia::render('Error', ['status' => $status])
                ->toResponse(request())->setStatusCode($status);
        })->name('preview.error');
    }
});

Route::fallback(function () {
    $request = request();

    if (! $request->isMethod('GET')) {
        return response()->noContent(404);
    }

    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return Inertia::render('Error', [
        'status' => 404,
        'homeUrl' => route('index'),
    ])
        ->toResponse($request)->setStatusCode(404);
});
