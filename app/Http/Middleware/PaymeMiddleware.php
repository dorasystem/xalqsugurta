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

        // Get request ID from JSON body (Payme uses JSON-RPC 2.0)
        $requestId = null;
        if ($request->isJson()) {
            $requestId = $request->json('id');
        } else {
            $body = json_decode($request->getContent(), true);
            $requestId = $body['id'] ?? null;
        }

        // Log incoming request (for debugging)
        Log::info('PayMe request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'has_auth' => !empty($authorization),
            'request_id' => $requestId,
        ]);

        // Helper function to return error response in Payme format
        $returnError = function ($requestId) {
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
                'id' => $requestId
            ], 200); // Payme expects 200 status with error in body
        };

        // Check if Authorization header exists and matches Basic Auth format
        if (!$authorization || !preg_match('/^\s*Basic\s+(\S+)\s*$/i', $authorization, $matches)) {
            Log::warning('PayMe: Missing or invalid Authorization header');

            return $returnError($requestId);
        }

        // Decode base64 credentials
        $decodedCredentials = base64_decode($matches[1], true);

        // Check if decode was successful
        if ($decodedCredentials === false) {
            Log::error('PayMe: Failed to decode credentials');

            return $returnError($requestId);
        }

        // Split username and password
        $credentials = explode(':', $decodedCredentials, 2);

        if (count($credentials) !== 2) {
            Log::error('PayMe: Invalid credentials format');

            return $returnError($requestId);
        }

        list($username, $password) = $credentials;

        // Detect test mode from header (Payme test sandbox sends "test-operation: Paycom")
        $isTestMode = $request->header('test-operation') === 'Paycom'
            || config('services.payme.test_mode', false);

        // Get expected credentials based on mode
        // Test mode: username is "Paycom", password is test_secret_key
        // Production mode: username is kassa_id, password is production_secret_key
        if ($isTestMode) {
            $expectedUsername = 'Paycom';
            $expectedPassword = config('services.payme.test_secret_key')
                ?: config('services.payme.secret_key')
                ?: 'trNM0xMZv0bzrNws7E19mgMSCbbjEKxSz#j0';
        } else {
            $expectedUsername = config('services.payme.kassa_id')
                ?: config('services.payme.merchant_id')
                ?: '68f7581688f28864c066266f';
            $expectedPassword = config('services.payme.production_secret_key')
                ?: config('services.payme.secret_key')
                ?: 'YpR&UdoV2@pGoqgqwn#gIX3uCqzxhAwh7z5n@';
        }

        // Verify credentials
        if ($username !== $expectedUsername || $password !== $expectedPassword) {
            Log::warning('PayMe: Invalid credentials', [
                'provided_username' => $username,
                'expected_username' => $expectedUsername,
                'is_test_mode' => $isTestMode,
                'test_operation_header' => $request->header('test-operation'),
                'ip' => $request->ip(),
            ]);

            return $returnError($requestId);
        }

        // Log successful authentication
        Log::info('PayMe: Authentication successful');

        return $next($request);
    }
}
