<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Vacancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Career section (/{locale}/career): open vacancies and a CV form, handled in the admin panel */
final class CareerController extends Controller
{
    private const MAX_FILE_KB = 5120;

    public function index(string $locale): View
    {
        return view('pages.career.index', [
            'vacancies' => Vacancy::open()->orderBy('sort_order')->orderByDesc('published_at')->get(),
            'locale'    => $locale,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $vacancy = Vacancy::open()->where('slug', $slug)->firstOrFail();

        return view('pages.career.show', [
            'vacancy' => $vacancy,
            'body'    => $vacancy->body($locale),
            'locale'  => $locale,
        ]);
    }

    public function apply(Request $request, string $locale): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect()->route('career.index', ['locale' => $locale]);
        }

        $phone = preg_replace('/\D/', '', (string) $request->input('phone'));
        $request->merge(['phone' => strlen($phone) === 9 ? '998' . $phone : $phone]);

        $data = $request->validate([
            'vacancy_id' => ['nullable', 'integer'],
            'name'       => ['required', 'string', 'max:100'],
            'phone'      => ['required', 'regex:/^998[0-9]{9}$/'],
            'email'      => ['nullable', 'email', 'max:150'],
            'message'    => ['nullable', 'string', 'max:2000'],
            'resume'     => ['required', 'file', 'mimes:pdf,doc,docx', 'max:' . self::MAX_FILE_KB],
        ], ['phone.regex' => __t('messages.flow.phone_help')]);

        // Only an open vacancy can be applied for; anything else is a general CV
        $vacancy = isset($data['vacancy_id']) ? Vacancy::open()->find($data['vacancy_id']) : null;

        $application = JobApplication::create([
            'vacancy_id' => $vacancy?->id,
            'name'       => $data['name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'] ?? null,
            'message'    => $data['message'] ?? null,
            'status'     => JobApplication::STATUS_NEW,
            'locale'     => $locale,
        ]);

        $file = $request->file('resume');
        $application->update([
            'file_path' => $file->store('resumes/' . $application->id, 'local'),
            'file_name' => mb_substr($file->getClientOriginalName(), 0, 150),
            'file_size' => $file->getSize(),
        ]);

        Log::info('Job application received', ['id' => $application->id, 'vacancy' => $vacancy?->id]);

        $back = $vacancy
            ? route('career.show', ['locale' => $locale, 'slug' => $vacancy->slug])
            : route('career.index', ['locale' => $locale]);

        return redirect()->to($back . '#apply')->with('status', __t('messages.career.sent'));
    }
}
