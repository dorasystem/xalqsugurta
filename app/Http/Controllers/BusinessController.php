<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use App\Models\InfoPage;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * "For business" section page (/{locale}/business): products sold online to organizations,
 * products offered on individual terms (a request staff handle in the admin panel) and the
 * company documents partners look for.
 */
final class BusinessController extends Controller
{
    /** Online products for organizations, in this order (OSAGO: organizations as owners) */
    private const ONLINE = ['osgor', 'osgop', 'osago'];

    /** Products of the old site's corporate catalogue sold on individual terms (messages.sections.products.*) */
    public const OFFERS = ['all_risks', 'collective', 'osago_corp', 'other'];

    public function index(string $locale): View
    {
        $products = Product::where('is_active', true)->whereIn('route', self::ONLINE)->get()->keyBy('route');

        return view('pages.business', [
            'online'    => collect(self::ONLINE)->map(fn (string $r) => $products->get($r))->filter()->values(),
            'offers'    => self::OFFERS,
            'locale'    => $locale,
            'documents' => [
                'tenders'              => route('tenders.index', ['locale' => $locale]),
                'financial-statements' => InfoPage::link('financial-statements', $locale),
                'essential-facts'      => InfoPage::link('essential-facts', $locale),
                'licenses'             => InfoPage::link('licenses', $locale),
            ],
        ]);
    }

    /** Request for an individual offer: stored as a call-back request with the "corporate" topic */
    public function request(Request $request, string $locale): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect()->route('business', ['locale' => $locale]);
        }

        $phone = preg_replace('/\D/', '', (string) $request->input('phone'));
        $request->merge(['phone' => strlen($phone) === 9 ? '998' . $phone : $phone]);

        $data = $request->validate([
            'company' => ['required', 'string', 'max:150'],
            'inn'     => ['nullable', 'digits:9'],
            'product' => ['required', 'in:' . implode(',', self::OFFERS)],
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['required', 'regex:/^998[0-9]{9}$/'],
            'message' => ['nullable', 'string', 'max:1000'],
        ], ['phone.regex' => __t('messages.flow.phone_help')]);

        $lines = [
            __('messages.business_page.form.company', [], 'uz') . ': ' . $data['company'],
            __('messages.business_page.form.inn', [], 'uz') . ': ' . ($data['inn'] ?? '—'),
            __('messages.business_page.form.product', [], 'uz') . ': ' . __('messages.sections.products.' . $data['product'], [], 'uz'),
        ];
        if (filled($data['message'] ?? null)) {
            $lines[] = $data['message'];
        }

        CallbackRequest::create([
            'name'    => $data['name'],
            'phone'   => $data['phone'],
            'topic'   => CallbackRequest::TOPIC_CORPORATE,
            'message' => implode("\n", $lines),
            'status'  => CallbackRequest::STATUS_NEW,
            'locale'  => $locale,
        ]);

        Log::info('Corporate offer requested', ['product' => $data['product']]);

        return redirect()->to(route('business', ['locale' => $locale]) . '#offer')
            ->with('status', __t('messages.business_page.form.sent'));
    }
}
