# PayMe Payment Gateway Integration

## 📋 Overview

This document describes the PayMe payment gateway integration for the insurance application.

---

## 🔧 Configuration

### 1. Environment Variables

Add the following to your `.env` file:

```env
# PayMe Payment Gateway Configuration
PAYME_MERCHANT_ID=Paycom
PAYME_SECRET_KEY=eZvBMM2GkiOP?Izbx3Fut3N1dNM0aRs1D&c8
PAYME_KASSA_ID=68f7581688f28864c066266f
PAYME_ENDPOINT=https://checkout.paycom.uz
PAYME_TEST_MODE=false
```

### 2. Configuration File

Configuration is stored in `config/services.php`:

```php
'payme' => [
    'merchant_id' => env('PAYME_MERCHANT_ID', 'Paycom'),
    'secret_key' => env('PAYME_SECRET_KEY'),
    'kassa_id' => env('PAYME_KASSA_ID', '68f7581688f28864c066266f'),
    'endpoint' => env('PAYME_ENDPOINT', 'https://checkout.paycom.uz'),
    'test_mode' => env('PAYME_TEST_MODE', false),
],
```

---

## 🔐 Security - PayMe Middleware

### Middleware Location
`app/Http/Middleware/PaymeMiddleware.php`

### What it does:
1. ✅ Validates PayMe Basic Authentication
2. ✅ Decodes Base64 credentials
3. ✅ Verifies merchant credentials
4. ✅ Logs authentication attempts
5. ✅ Returns proper error responses

### How it works:

```php
// PayMe sends Authorization header:
// Authorization: Basic base64(merchant_id:secret_key)

// Middleware validates:
1. Header exists and format is correct
2. Base64 decode succeeds
3. Username (merchant_id) matches
4. Password (secret_key) matches
```

### Error Responses:

```json
{
    "jsonrpc": "2.0",
    "error": {
        "code": -32504,
        "message": {
            "uz": "Avtorizatsiyadan o'tishda xatolik",
            "ru": "Ошибка аутентификации",
            "en": "Auth error"
        }
    },
    "id": null
}
```

---

## 🛣️ Routes

### API Route Configuration
`routes/api.php`

```php
// PayMe callback endpoint
Route::post('payme/callback', [PaymeController::class, 'handleCallback'])
    ->middleware('payme')  // ← Security middleware
    ->name('payment.payme.callback');
```

### Endpoint URL:
```
POST https://your-domain.com/api/payme/callback
```

---

## 🔑 Credentials Breakdown

| Parameter | Value | Description |
|-----------|-------|-------------|
| **Merchant ID** | `Paycom` | Your merchant identifier |
| **Secret Key** | `eZvBMM2GkiOP?Izbx3Fut3N1dNM0aRs1D&c8` | Secret key for authentication |
| **Kassa ID** | `68f7581688f28864c066266f` | Cash register ID |
| **Endpoint** | `https://checkout.paycom.uz` | PayMe checkout URL |

---

## 📝 Usage in Controller

### Get Kassa ID:
```php
$kassaId = config('services.payme.kassa_id');
// Returns: 68f7581688f28864c066266f
```

### Generate Payment URL:
```php
public function redirectToPayme($orderId, $amount)
{
    $merchantId = config('services.payme.kassa_id');
    $callbackUrl = route('payment.payme.callback');
    
    // Encode order info
    $params = base64_encode(json_encode([
        'merchant_id' => $merchantId,
        'amount' => $amount * 100, // Convert to tiyin
        'account' => [
            'order_id' => $orderId,
        ],
    ]));
    
    $paymentUrl = config('services.payme.endpoint') . '/' . $params;
    
    return redirect($paymentUrl);
}
```

---

## 🧪 Testing Middleware

### Test with cURL:

```bash
# Correct credentials (should work)
curl -X POST https://your-domain.com/api/payme/callback \
  -H "Authorization: Basic $(echo -n 'Paycom:eZvBMM2GkiOP?Izbx3Fut3N1dNM0aRs1D&c8' | base64)" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "CheckPerformTransaction",
    "params": {},
    "id": 1
  }'

# Wrong credentials (should fail)
curl -X POST https://your-domain.com/api/payme/callback \
  -H "Authorization: Basic $(echo -n 'Wrong:Credentials' | base64)" \
  -H "Content-Type: application/json" \
  -d '{}'
```

### Expected Responses:

**✅ Success (passes middleware):**
```json
{
    "jsonrpc": "2.0",
    "result": {...},
    "id": 1
}
```

**❌ Failure (blocked by middleware):**
```json
{
    "jsonrpc": "2.0",
    "error": {
        "code": -32504,
        "message": {
            "uz": "Avtorizatsiyadan o'tishda xatolik",
            "ru": "Ошибка аутентификации",
            "en": "Auth error"
        }
    },
    "id": null
}
```

---

## 📊 Logging

Middleware logs all authentication attempts:

```php
// Successful authentication
Log::info('PayMe: Authentication successful');

// Failed authentication
Log::warning('PayMe: Invalid credentials', [
    'provided_username' => $username,
    'ip' => $request->ip(),
]);

// Missing header
Log::warning('PayMe: Missing or invalid Authorization header');
```

**View logs:**
```bash
tail -f storage/logs/laravel.log | grep PayMe
```

---

## 🔒 Security Best Practices

### ✅ DO:
1. ✅ Keep `PAYME_SECRET_KEY` in `.env` file (never commit)
2. ✅ Use HTTPS in production
3. ✅ Monitor logs for suspicious activity
4. ✅ Rotate credentials periodically
5. ✅ Validate all incoming data

### ❌ DON'T:
1. ❌ Commit credentials to git
2. ❌ Use same credentials for test and production
3. ❌ Disable middleware in production
4. ❌ Log sensitive data (passwords, cards)
5. ❌ Trust client-side data

---

## 🐛 Troubleshooting

### Problem: "Auth error" response

**Possible causes:**
1. Wrong merchant ID or secret key in `.env`
2. Missing or malformed Authorization header
3. Base64 encoding issue
4. Credentials mismatch

**Solutions:**
```bash
# 1. Check .env file
cat .env | grep PAYME

# 2. Clear config cache
php artisan config:clear

# 3. Test credentials
echo -n 'Paycom:eZvBMM2GkiOP?Izbx3Fut3N1dNM0aRs1D&c8' | base64

# 4. Check logs
tail -f storage/logs/laravel.log
```

---

### Problem: Middleware not working

**Solutions:**
```bash
# 1. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 2. Check middleware is registered
php artisan route:list | grep payme

# 3. Verify middleware file exists
ls -la app/Http/Middleware/PaymeMiddleware.php
```

---

## 📞 PayMe Support

- **Documentation:** https://developer.help.paycom.uz/
- **Support:** support@paycom.uz
- **Phone:** +998 71 200 01 10

---

## ✅ Checklist

Before going to production:

- [ ] `.env` configured with production credentials
- [ ] Middleware tested with real PayMe requests
- [ ] Logging configured and monitored
- [ ] HTTPS enabled
- [ ] Callback URL registered in PayMe dashboard
- [ ] Error handling tested
- [ ] Security review completed

---

## 📚 Related Files

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Payments/
│   │       └── PayMe/
│   │           └── PaymeController.php
│   └── Middleware/
│       └── PaymeMiddleware.php       ← Middleware
├── Models/
│   └── Order.php
config/
└── services.php                       ← Configuration
routes/
└── api.php                           ← Route with middleware
bootstrap/
└── app.php                           ← Middleware registration
```

---

## 🎯 Summary

✅ **Middleware Created:** `PaymeMiddleware.php`
✅ **Configuration Added:** `config/services.php`
✅ **Middleware Registered:** `bootstrap/app.php`
✅ **Route Protected:** `routes/api.php`
✅ **Credentials Configured:** Kassa ID `68f7581688f28864c066266f`

**Security Status:** 🔒 **PROTECTED**

All PayMe callbacks are now secured with Basic Authentication middleware!

