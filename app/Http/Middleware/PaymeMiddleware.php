<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PaymeMiddleware
{
    /**
     * Handle an incoming request from PayMe
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get Authorization header
        $authorization = $request->header('Authorization');

        // Log incoming request (for debugging)
        Log::info('PayMe request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'has_auth' => !empty($authorization),
        ]);

        // Check if Authorization header exists and matches Basic Auth format
        if (!$authorization || !preg_match('/^\s*Basic\s+(\S+)\s*$/i', $authorization, $matches)) {
            Log::warning('PayMe: Missing or invalid Authorization header');

            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32504,
                    'message' => [
                        "uz" => "Avtorizatsiyadan o'tishda xatolik",
                        "ru" => "Ошибка аутентификации",
                        "en" => "Auth error"
                    ]
                ],
                'id' => $request->input('id')
            ], 401);
        }

        // Decode base64 credentials
        $decodedCredentials = base64_decode($matches[1]);

        // Check if decode was successful
        if ($decodedCredentials === false) {
            Log::error('PayMe: Failed to decode credentials');

            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32504,
                    'message' => [
                        "uz" => "Avtorizatsiyadan o'tishda xatolik",
                        "ru" => "Ошибка аутентификации",
                        "en" => "Auth error"
                    ]
                ],
                'id' => $request->input('id')
            ], 401);
        }

        // Split username and password
        $credentials = explode(':', $decodedCredentials, 2);

        if (count($credentials) !== 2) {
            Log::error('PayMe: Invalid credentials format');

            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32504,
                    'message' => [
                        "uz" => "Avtorizatsiyadan o'tishda xatolik",
                        "ru" => "Ошибка аутентификации",
                        "en" => "Auth error"
                    ]
                ],
                'id' => $request->input('id')
            ], 401);
        }

        list($username, $password) = $credentials;

        // Get expected credentials from config
        $expectedUsername = config('services.payme.merchant_id', 'Paycom');
        $expectedPassword = config('services.payme.secret_key');

        // Verify credentials
        if ($username !== $expectedUsername || $password !== $expectedPassword) {
            Log::warning('PayMe: Invalid credentials', [
                'provided_username' => $username,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32504,
                    'message' => [
                        "uz" => "Avtorizatsiyadan o'tishda xatolik",
                        "ru" => "Ошибка аутентификации",
                        "en" => "Auth error"
                    ]
                ],
                'id' => $request->input('id')
            ], 401);
        }

        // Log successful authentication
        Log::info('PayMe: Authentication successful');

        return $next($request);
    }
}
