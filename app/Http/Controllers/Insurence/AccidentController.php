<?php

namespace App\Http\Controllers\Insurence;

use App\Actions\Insurence\ProcessAccidentApplicationAction;
use App\DTOs\AccidentApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\AccidentApplicationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class AccidentController extends Controller
{
    public function main(): View
    {
        return view('pages.insurence.accident.main');
    }

    public function application(
        AccidentApplicationRequest $request,
        ProcessAccidentApplicationAction $action
    ): View|RedirectResponse {
        try {
            // Create DTO from validated request data
            $applicationData = AccidentApplicationData::fromRequest($request->validated());

            dd($applicationData);

            // Execute the action to send data to API
            $result = $action->execute($applicationData);

            if (!$result['success']) {
                return back()
                    ->withErrors(['error' => $result['error']])
                    ->withInput();
            }

            // Pass the structured data to the view
            return view('pages.insurence.accident.application', [
                'applicationData' => $applicationData->toArray(),
                'apiResponse' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Xatolik yuz berdi: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function payment(): View
    {
        return view('pages.insurence.accident.payment');
    }

    public function calculation(): View
    {
        return view('pages.insurence.accident.calculation');
    }
}
