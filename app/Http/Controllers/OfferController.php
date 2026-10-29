<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use App\Models\OfferProduct;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Products sold on application (/{locale}/insurance/{slug}, design "A": dark hero with the request
 * card, what it covers, main terms, ways to apply). A request becomes a CallbackRequest with the
 * "product" topic, so staff handle it in Murojaatlar → Qayta qo'ng'iroqlar.
 */
final class OfferController extends Controller
{
    public function show(string $locale, string $slug): View
    {
        $product = self::find($slug);

        return view('pages.offers.show', ['product' => $product, 'locale' => $locale]);
    }

    public function request(Request $request, string $locale, string $slug): RedirectResponse
    {
        $product = self::find($slug);
        $back = route('offers.show', ['locale' => $locale, 'slug' => $slug]) . '#apply';

        if (filled($request->input('website'))) {
            return redirect()->to($back);
        }

        $phone = preg_replace('/\D/', '', (string) $request->input('phone'));
        $request->merge(['phone' => strlen($phone) === 9 ? '998' . $phone : $phone]);

        $rules = [
            'name'      => ['required', 'string', 'max:100'],
            'company'   => ['required', 'string', 'max:150'],
            'phone'     => ['required', 'regex:/^998[0-9]{9}$/'],
            'members'   => ['required', 'integer', 'between:1,5'],
            'employees' => ['required', 'integer', 'between:1,100000'],
        ];
        $fields = $product->formFields();
        $data = $request->validate(
            array_intersect_key($rules, array_flip($fields)),
            ['phone.regex' => __t('messages.flow.phone_help')],
        );

        $labels = [
            'company'   => __('messages.offer.company', [], 'uz'),
            'members'   => __('messages.offer.members', [], 'uz'),
            'employees' => __('messages.offer.employees', [], 'uz'),
        ];
        $lines = [__('messages.offer.product', [], 'uz') . ': ' . $product->text('title', 'uz')];
        foreach ($labels as $field => $label) {
            if (isset($data[$field])) {
                $lines[] = $label . ': ' . ($field === 'members' && (int) $data[$field] === 5 ? '5+' : $data[$field]);
            }
        }

        CallbackRequest::create([
            'name'    => $data['name'] ?? $data['company'],
            'phone'   => $data['phone'],
            'topic'   => CallbackRequest::TOPIC_PRODUCT,
            'message' => implode("\n", $lines),
            'status'  => CallbackRequest::STATUS_NEW,
            'locale'  => $locale,
        ]);

        Log::info('Product application received', ['product' => $slug]);

        return redirect()->to($back)->with('status', __t('messages.offer.sent'));
    }

    private static function find(string $slug): OfferProduct
    {
        try {
            return OfferProduct::where('slug', $slug)->where('is_active', true)->firstOrFail();
        } catch (QueryException) {
            abort(404);   // not migrated yet
        }
    }
}
