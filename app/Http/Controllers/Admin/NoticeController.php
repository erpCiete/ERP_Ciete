<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\HomeNoticeCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'notices'     => 'required|array|max:5',
            'updates'     => 'required|array|max:5',
            'companyNews' => 'required|array|max:5',

            'notices.*.id'     => 'nullable|string|max:120',
            'notices.*.es'     => 'required|string|max:300',
            'notices.*.en'     => 'required|string|max:300',
            'notices.*.featured' => 'nullable|boolean',
            'updates.*.id'     => 'nullable|string|max:120',
            'updates.*.es'     => 'required|string|max:300',
            'updates.*.en'     => 'required|string|max:300',
            'updates.*.featured' => 'nullable|boolean',
            'companyNews.*.id' => 'nullable|string|max:120',
            'companyNews.*.es' => 'required|string|max:300',
            'companyNews.*.en' => 'required|string|max:300',
            'companyNews.*.featured' => 'nullable|boolean',
        ]);

        HomeNoticeCatalog::save($request->only(HomeNoticeCatalog::categories()));

        return back();
    }

    public static function load(): array
    {
        return HomeNoticeCatalog::load();
    }
}
