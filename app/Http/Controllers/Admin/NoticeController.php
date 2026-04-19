<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    private const FILE = 'notices.json';

    private const CATEGORIES = ['notices', 'updates', 'companyNews'];

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'notices'     => 'required|array|max:5',
            'updates'     => 'required|array|max:5',
            'companyNews' => 'required|array|max:5',

            'notices.*.es'     => 'required|string|max:300',
            'notices.*.en'     => 'required|string|max:300',
            'updates.*.es'     => 'required|string|max:300',
            'updates.*.en'     => 'required|string|max:300',
            'companyNews.*.es' => 'required|string|max:300',
            'companyNews.*.en' => 'required|string|max:300',
        ]);

        $data = $request->only(self::CATEGORIES);

        Storage::put(self::FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return back();
    }

    public static function load(): array
    {
        if (! Storage::exists(self::FILE)) {
            return ['notices' => [], 'updates' => [], 'companyNews' => []];
        }

        $raw = Storage::get(self::FILE);
        $data = json_decode($raw, true);

        if (! is_array($data)) {
            return ['notices' => [], 'updates' => [], 'companyNews' => []];
        }

        return [
            'notices'     => $data['notices'] ?? [],
            'updates'     => $data['updates'] ?? [],
            'companyNews' => $data['companyNews'] ?? [],
        ];
    }
}
