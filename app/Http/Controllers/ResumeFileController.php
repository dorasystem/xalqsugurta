<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** CV files for staff: signed link from the admin panel + admin session (files are private) */
final class ResumeFileController extends Controller
{
    public function __invoke(JobApplication $application): StreamedResponse
    {
        abort_unless(auth()->user()?->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')), 403);
        abort_unless($application->file_path && Storage::disk('local')->exists($application->file_path), 404);

        return Storage::disk('local')->download($application->file_path, $application->file_name ?? basename($application->file_path));
    }
}
