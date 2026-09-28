<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** "Call me back" (/{locale}/callback): stored for staff, handled in the admin panel */
final class CallbackController extends Controller
{
    public function create(): View
    {
        return view('pages.callback', ['topics' => CallbackRequest::TOPICS]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect()->route('callback', ['locale' => getCurrentLocale()]);
        }

        $phone = preg_replace('/\D/', '', (string) $request->input('phone'));
        $request->merge(['phone' => strlen($phone) === 9 ? '998' . $phone : $phone]);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['required', 'regex:/^998[0-9]{9}$/'],
            'topic'   => ['nullable', 'in:' . implode(',', CallbackRequest::TOPICS)],
            'message' => ['nullable', 'string', 'max:1000'],
        ], ['phone.regex' => __t('messages.flow.phone_help')]);

        // One open request per phone is enough: repeated clicks update it
        CallbackRequest::updateOrCreate(
            ['phone' => $data['phone'], 'status' => CallbackRequest::STATUS_NEW],
            $data + ['locale' => getCurrentLocale()],
        );

        Log::info('Callback requested');

        return redirect()->route('callback', ['locale' => getCurrentLocale()])
            ->with('status', __t('messages.callback.sent'));
    }
}
