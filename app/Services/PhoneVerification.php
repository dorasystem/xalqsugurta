<?php

namespace App\Services;

use App\Services\Sms\EskizClient;
use App\Services\Sms\SmsException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * One-time SMS codes that prove a visitor owns a phone number ("Mening polislarim").
 * The code is stored hashed for CODE_TTL seconds and dies after MAX_ATTEMPTS wrong tries;
 * sending is limited per phone and per IP so nobody can flood a number or run up the SMS bill.
 */
final class PhoneVerification
{
    public const CODE_TTL      = 300;
    public const MAX_ATTEMPTS  = 5;
    public const RESEND_AFTER  = 60;
    private const PER_PHONE    = 3;     // codes per phone per 30 minutes
    private const PER_IP       = 10;    // codes per IP per hour

    public const SENT          = 'sent';
    public const TOO_SOON      = 'too_soon';
    public const TOO_MANY      = 'too_many';
    public const UNAVAILABLE   = 'unavailable';

    public function __construct(private readonly EskizClient $sms) {}

    /** One of the constants above; SENT means an SMS went out */
    public function send(string $phone, string $ip): string
    {
        if (!SmsSettings::ready()) {
            return self::UNAVAILABLE;
        }

        if (Cache::has($this->key($phone, 'resend'))) {
            return self::TOO_SOON;
        }

        if (RateLimiter::tooManyAttempts('otp-phone:' . $phone, self::PER_PHONE)
            || RateLimiter::tooManyAttempts('otp-ip:' . $ip, self::PER_IP)) {
            return self::TOO_MANY;
        }

        $code = (string) random_int(100000, 999999);

        try {
            $this->sms->send($phone, str_replace('{code}', $code, (string) config('services.eskiz.template')));
        } catch (SmsException) {
            return self::UNAVAILABLE;
        }

        RateLimiter::hit('otp-phone:' . $phone, 1800);
        RateLimiter::hit('otp-ip:' . $ip, 3600);

        $expires = now()->addSeconds(self::CODE_TTL);
        Cache::put($this->key($phone, 'code'), ['hash' => Hash::make($code), 'attempts' => 0, 'expires' => $expires->timestamp], $expires);
        Cache::put($this->key($phone, 'resend'), true, self::RESEND_AFTER);

        return self::SENT;
    }

    /** True once for the right code; the code is used up either way after MAX_ATTEMPTS */
    public function verify(string $phone, string $code): bool
    {
        $key   = $this->key($phone, 'code');
        $entry = Cache::get($key);

        if (!is_array($entry)) {
            return false;
        }

        if (Hash::check($code, $entry['hash'])) {
            Cache::forget($key);

            return true;
        }

        // A wrong try must not extend the code's life
        $entry['attempts']++;
        $entry['attempts'] >= self::MAX_ATTEMPTS
            ? Cache::forget($key)
            : Cache::put($key, $entry, \Carbon\Carbon::createFromTimestamp($entry['expires']));

        return false;
    }

    private function key(string $phone, string $what): string
    {
        return 'otp:' . $what . ':' . $phone;
    }
}
