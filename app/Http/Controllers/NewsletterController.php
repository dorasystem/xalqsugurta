<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Footer "subscribe to the newsletter" form: the email is stored for staff (no mailing is sent yet) */
final class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // Honeypot: bots fill every field
        if (filled($request->input('website'))) {
            return back();
        }

        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $data = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email', 'max:150'],
        ]);

        NewsletterSubscriber::firstOrCreate(['email' => $data['email']], ['locale' => getCurrentLocale()]);

        return back()->with('success', __t('messages.newsletter_subscribed'));
    }
}
