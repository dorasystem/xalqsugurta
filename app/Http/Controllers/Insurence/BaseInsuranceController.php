<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\Provider\ProviderApiTrait;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class BaseInsuranceController extends Controller
{
    use ProviderApiTrait, HandlesInsuranceErrors;

    abstract protected function getProductKey(): string;

    public function __construct(protected readonly OrderService $orderService) {}

    // ─── Session helpers ──────────────────────────────────────────────────────

    protected function sess(string $key, mixed $default = null): mixed
    {
        return session($this->getProductKey() . '.' . $key, $default);
    }

    protected function putSess(string $key, mixed $value): void
    {
        session([$this->getProductKey() . '.' . $key => $value]);
    }

    protected function clearSess(): void
    {
        session()->forget($this->getProductKey());
    }

    // ─── Phone normalizer ─────────────────────────────────────────────────────

    protected function cleanPhone(?string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone ?? '');

        if (str_starts_with($phone, '00998')) {
            $phone = substr($phone, 2);
        }

        if (strlen($phone) === 9) {
            $phone = '998' . $phone;
        }

        return $phone;
    }

    // ─── Person data normalizer ───────────────────────────────────────────────

    protected function normalizePerson(array $p, ?Request $r = null): array
    {
        return [
            'pinfl'                => $p['currentPinfl']           ?? '',
            'passport_seria'       => strtoupper($r?->input('passport_seria') ?? ''),
            'passport_number'      => $r?->input('passport_number') ?? '',
            'passport_issue_date'  => $p['docIssueDate'] ?? $p['issueDate'] ?? '',
            'passport_issued_by'   => $p['docIssuedBy']  ?? $p['issuedBy']  ?? '',
            'birth_date'           => $r?->input('birth_date') ?? ($p['birthDate'] ?? ''),
            'lastname'             => $p['lastNameLatin']   ?? $p['lastName']   ?? '',
            'firstname'            => $p['firstNameLatin']  ?? $p['firstName']  ?? '',
            'middlename'           => $p['middleNameLatin'] ?? $p['middleName'] ?? '',
            'gender'               => ($p['gender'] ?? '') == '1' ? 'm' : 'f',
            'address'              => $p['address']  ?? '',
            'region_id'            => (int) ($p['regionId']   ?? 10),
            'district_id'          => (int) ($p['districtId'] ?? 0),
            'phone'                => $this->cleanPhone($p['phone'] ?? $r?->input('phone') ?? ''),
            'resident_type'        => 1,
            'country_id'           => 210,
        ];
    }

    // ─── Product loader ───────────────────────────────────────────────────────

    protected function getProduct(): ?Product
    {
        return Product::where('route', $this->getProductKey())->first();
    }

    // ─── Offerta validation rule ──────────────────────────────────────────────

    protected function offertaRule(): array
    {
        return ['required', 'accepted'];
    }

    // ─── Session guard ────────────────────────────────────────────────────────

    protected function requireSession(string $key, string $redirectRoute): void
    {
        if (!$this->sess($key)) {
            redirect()->route($redirectRoute, ['locale' => getCurrentLocale()])->send();
            exit;
        }
    }

    // ─── Shared AJAX: Find Person ─────────────────────────────────────────────

    public function findPerson(Request $request): JsonResponse
    {
        $request->validate([
            'passport_seria'  => ['required', 'string', 'max:4'],
            'passport_number' => ['required', 'digits:7'],
            'birth_date'      => ['required', 'date', 'before:today'],
        ]);

        try {
            $person = $this->findPersonByPassport(
                strtoupper($request->input('passport_seria')) . $request->input('passport_number'),
                $request->input('birth_date')
            );
        } catch (ProviderException $e) {
            return response()->json(['success' => false, 'message' => __('messages.person_not_found')], 422);
        }

        if (empty($person['currentPinfl'] ?? null)) {
            return response()->json(['success' => false, 'message' => __('messages.person_not_found')], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'pinfl'               => $person['currentPinfl'],
                'passport_seria'      => strtoupper($request->input('passport_seria')),
                'passport_number'     => $request->input('passport_number'),
                'passport_issue_date' => $person['docIssueDate'] ?? $person['issueDate'] ?? '',
                'passport_issued_by'  => $person['docIssuedBy']  ?? $person['issuedBy']  ?? '',
                'firstname'           => $person['firstNameLatin']  ?? $person['firstName']  ?? '',
                'lastname'            => $person['lastNameLatin']   ?? $person['lastName']   ?? '',
                'middlename'          => $person['middleNameLatin'] ?? $person['middleName'] ?? '',
                'birth_date'          => $request->input('birth_date'),
                'gender'              => ($person['gender'] ?? '') == '1' ? 'm' : 'f',
                'address'             => $person['address']    ?? '',
                'region_id'           => (int) ($person['regionId']   ?? 10),
                'district_id'         => (int) ($person['districtId'] ?? 0),
                'phone'               => $this->cleanPhone($person['phone'] ?? ''),
                'resident_type'       => 1,
                'country_id'          => 210,
            ],
        ]);
    }

    // ─── Create order and redirect to payment ─────────────────────────────────

    protected function createOrderAndRedirect(array $data, string $productKey): RedirectResponse
    {
        // Embed the product key so payment handlers can identify the product
        if (isset($data['insurances_data']) && is_array($data['insurances_data'])) {
            $data['insurances_data']['_product_key'] = $productKey;
        }

        $order = $this->orderService->createOrder(array_merge([
            'status' => Order::STATUS_NEW,
        ], $data));

        $this->clearSess();

        return redirect()->route('payment.show', [
            'locale'  => getCurrentLocale(),
            'orderId' => $order->id,
        ]);
    }
}
