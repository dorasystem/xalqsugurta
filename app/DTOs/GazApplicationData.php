<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

use function Symfony\Component\Clock\now;

final class GazApplicationData
{
    public function __construct(
        public array $customer,
        public array $loanInfo,
        public string $subject,
        public int $insuranceAmount,
        public float $insurancePremium,
        public string $paymentStartDate,
        public string $paymentEndDate,
    ) {}

    public static function fromRequest(array $data, array $owner, array $property): self
    {
        // Extract owner information from session
        $pinfl = $owner['currentPinfl'] ?? '';
        $firstName = $owner['firstNameLatin'] ?? '';
        $lastName = $owner['lastNameLatin'] ?? '';
        $middleName = $owner['middleNameLatin'] ?? '';
        $fullName = trim("$lastName $firstName $middleName");

        // Format birth date: "2002-04-14" -> "14.04.2002"
        $birthDate = $owner['birthDate'] ?? '';
        $birthDateFormatted = $birthDate ? Carbon::parse($birthDate)->format('d.m.Y') : '';

        // Extract passport from currentDocument
        $currentDocument = $owner['currentDocument'] ?? '';
        preg_match('/([A-Z]+)(\d+)/', $currentDocument, $docParts);
        $passport = $currentDocument; // Full document number

        // Build customer data
        $customer = [
            'address' => $owner['address'] ?? '',
            'birth_date' => $birthDateFormatted,
            'bxm' => 0, // Default value, can be updated if needed
            'full_name' => $fullName,
            'gender' => (int) ($owner['gender'] ?? 1),
            'inn' => 0, // Default value, can be updated if needed
            'oked' => 0, // Default value, can be updated if needed
            'passport' => $passport,
            'phone' => $data['owner']['phoneNumber'] ?? '',
            'pinfl' => $pinfl,
            'ras_sum' => '0', // Default value, can be updated if needed
            'representativename' => '', // Default empty
            'subj_mfo' => 0, // Default value, can be updated if needed
        ];

        // Extract property information
        // Try to get cadastr_number from request data first, then from property session
        $cadastrNumber = $data['property']['cadastr_number']
            ?? $property['cadastr_number']
            ?? '';
        $sumBank = (int) ($data['insurance_amount'] ?? 0);

        // Format dates: "2026-01-01" -> "01.01.2026"
        $contractDate = $data['payment_start_date'] ?? '';
        $contractDateFormatted = $contractDate ? Carbon::parse($contractDate)->format('d.m.Y') : '';
        $endDate = $data['payment_end_date'] ?? '';
        $endDateFormatted = $endDate ? Carbon::parse($endDate)->format('d.m.Y') : '';

        // Build cadastr_info
        $cadastrInfo = [
            'address' => $property['address'] ?? $property['shortAddress'] ?? '',
            'building_type' => (int) ($property['tip'] ?? 1),
            'cadastr_issue_date' => $contractDateFormatted,
            'cadastr_number' => $cadastrNumber,
            'country' => 210, // Uzbekistan
            'description' => $property['address'] ?? $property['shortAddress'] ?? '',
            'districtid' => (int) ($property['districtId'] ?? 0),
            'is_foreign' => 0,
            'is_owner' => 1,
            'name' => $property['tipText'] ?? '',
            'note' => $property['vidText'] ?? '',
            'region_code' => (string) ($property['regionId'] ?? ''),
            'regionid' => (int) ($property['regionId'] ?? 0),
            'right_land_type' => 1, // Default value
            'subject_full_name' => $fullName,
            'sum_bank' => $sumBank,
        ];

        // Build loan_info
        $loanInfo = [
            'cadastr_info' => $cadastrInfo,
            'claim_id' => now(), // Default empty, can be updated if needed
            'contract_date' => $contractDateFormatted,
            'contract_number' => '', // Default empty, can be updated if needed
            'e_date' => $endDateFormatted,
            'loan_type' => '35', // Default value for gas balloon
            's_date' => $contractDateFormatted,
        ];

        // Calculate insurance premium: sum_bank * 0.5%
        $insurancePremium = round($sumBank * 0.005, 2);

        return new self(
            customer: $customer,
            loanInfo: $loanInfo,
            subject: 'P', // Property
            insuranceAmount: $sumBank,
            insurancePremium: $insurancePremium,
            paymentStartDate: $contractDate,
            paymentEndDate: $endDate,
        );
    }

    public function toArray(): array
    {
        return [
            'customer' => $this->customer,
            'loan_info' => $this->loanInfo,
            'subject' => $this->subject,
            'insuranceAmount' => $this->insuranceAmount,
            'insurancePremium' => $this->insurancePremium,
            'paymentStartDate' => $this->paymentStartDate,
            'paymentEndDate' => $this->paymentEndDate,
        ];
    }

    public function toApiFormat(): array
    {
        return [
            'customer' => $this->customer,
            'loan_info' => $this->loanInfo,
            'subject' => $this->subject,
        ];
    }
}
