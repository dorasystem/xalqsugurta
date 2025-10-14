<?php

namespace App\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

trait HandlesInsuranceErrors
{
    /**
     * Handle session data not found error
     */
    protected function handleSessionNotFound(string $insuranceType): RedirectResponse
    {
        Log::warning("{$insuranceType} storage: Session data not found");

        return redirect()->route("{$insuranceType}.main", ['locale' => getCurrentLocale()])
            ->with('error', __('errors.insurance.session_not_found'));
    }

    /**
     * Handle order creation error
     */
    protected function handleOrderCreationError(string $insuranceType, \Exception $e): RedirectResponse
    {
        Log::error("{$insuranceType} storage: Failed to create order", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()->with('error', __('errors.insurance.order_create_failed') . ': ' . $e->getMessage());
    }

    /**
     * Handle API request error
     */
    protected function handleApiError(string $insuranceType, string $errorMessage): RedirectResponse
    {
        Log::error("{$insuranceType}: API request failed", [
            'error' => $errorMessage,
        ]);

        return back()
            ->withErrors(['error' => __('errors.insurance.api_request_failed')])
            ->withInput();
    }

    /**
     * Handle general error
     */
    protected function handleGeneralError(string $insuranceType, \Exception $e, string $action = 'processing'): RedirectResponse
    {
        Log::error("{$insuranceType} {$action}: Error occurred", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()
            ->withErrors(['error' => __('errors.general_error') . ': ' . $e->getMessage()])
            ->withInput();
    }

    /**
     * Handle validation error
     */
    protected function handleValidationError(string $message): RedirectResponse
    {
        return back()
            ->withErrors(['error' => $message])
            ->withInput();
    }

    /**
     * Redirect with success message
     */
    protected function redirectWithSuccess(string $route, array $params, string $message): RedirectResponse
    {
        return redirect()->route($route, $params)->with('success', $message);
    }

    /**
     * Redirect back with success message
     */
    protected function backWithSuccess(string $message): RedirectResponse
    {
        return back()->with('success', $message);
    }
}
