<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FilePreviewController extends Controller
{
    public function preview(Request $request)
    {
        $path = trim($request->query('path', ''));

        if (!$this->isValidPublicPath($path)) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    public function download(Request $request)
    {
        $path = trim($request->query('path', ''));

        if (!$this->isValidPublicPath($path)) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path, basename($path));
    }

    protected function isValidPublicPath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (Str::contains($path, ['..', '\\'])) {
            return false;
        }

        return Str::startsWith($path, 'uploads/');
    }
}
