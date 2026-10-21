<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Claim attachments for staff: signed link from the admin panel + admin session (files are private) */
final class ClaimFileController extends Controller
{
    public function __invoke(Claim $claim, int $index): StreamedResponse
    {
        abort_unless(auth()->user()?->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')), 403);

        $file = ($claim->files ?? [])[$index] ?? null;
        abort_unless($file && Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name'] ?? basename($file['path']));
    }
}
