<?php

declare(strict_types=1);

namespace App\Services;

/**
 * OSAGO Price Calculator Service
 *
 * This service handles all price calculations on the server-side
 * to prevent client-side manipulation and ensure data integrity.
 */
final class OsagoPriceCalculator
{
    // Base insurance amount (40 million UZS)
    private const INSURANCE_AMOUNT = 80000000;

    // Default premium if calculation fails
    private const DEFAULT_AMOUNT = 384000;

    // Region coefficients (КТ - Territory coefficient)
    // According to new regulations 2026: Toshkent shahri va viloyati = 1.2, Boshqalar = 1.0
    private const REGION_TASHKENT = 1.2;
    private const REGION_OTHER = 1.0;

    // Vehicle type coefficients (ТБ - Annual base rate)
    // According to new regulations 2026:
    // 1. Yengil avtomobillar: 0.2
    // 2. Yuk avtomobillari: 0.35
    // 3. Avtobuslar va mikroavtobuslar: 0.4
    // 4. Tramvaylar, mototsikllar: 0.075
    private const VEHICLE_TYPES = [
        1 => 0.2,    // Yengil avtomobillar (was 0.1)
        2 => 0.2,    // Yengil avtomobillar (was 0.1)
        6 => 0.35,   // Yuk avtomobillari (was 0.12)
        9 => 0.4,    // Avtobuslar va mikroavtobuslar (was 0.12)
        15 => 0.075, // Tramvaylar, mototsikllar (was 0.04)
    ];

    // Period multipliers (КС - Seasonal coefficient)
    private const PERIOD_MULTIPLIERS = [
        '1' => 1.0,    // 12 months
        '0.7' => 0.7,  // 6 months
        '0.4' => 0.4,  // 3 months (not in new regulations, but kept for compatibility)
    ];

    // Driver limit multipliers (КБО - Driver restriction coefficient)
    // According to new regulations 2026: Unlimited = 2, Limited = 1
    private const DRIVER_UNLIMITED = 2;  // Changed from 3 to 2
    private const DRIVER_LIMITED = 1;

    // КБМ - Accident history coefficient (for limited drivers)
    // O'tgan 12 oy davomida sug'urta hodisalari
    private const ACCIDENT_COEF_NO_ACCIDENTS = 1.0;      // Birinchi marta yoki hodisa yo'q
    private const ACCIDENT_COEF_ONE_ACCIDENT = 1.3;      // 1 hodisa
    private const ACCIDENT_COEF_TWO_ACCIDENTS = 2.0;    // 2 hodisa
    private const ACCIDENT_COEF_THREE_OR_MORE = 3.0;   // 3 va undan ko'p

    // КВ - Driver experience coefficient (always 1.0)
    private const EXPERIENCE_COEF = 1.0;

    // КН - Violation coefficient (always 1.0)
    private const VIOLATION_COEF = 1.0;

    // КВЗ - Driver age coefficient (always 1.0)
    private const AGE_COEF = 1.0;

    /**
     * Calculate OSAGO insurance premium
     *
     * @param string $govNumber Government number (for region determination)
     * @param int $vehicleTypeId Vehicle type ID
     * @param float|string $period Insurance period (1, 0.7, 0.4)
     * @param string $driverLimit Driver limit type ('limited' or 'unlimited')
     * @return array ['amount' => int, 'insuranceAmount' => int]
     */
    public function calculate(
        string $govNumber,
        int $vehicleTypeId,
        float|string $period,
        string $driverLimit
    ): array {
        // Validate inputs
        $this->validateInputs($govNumber, $vehicleTypeId, $period, $driverLimit);

        // Determine region coefficient
        $regionCoef = $this->getRegionCoefficient($govNumber);

        // Get vehicle type coefficient
        $vehicleCoef = $this->getVehicleTypeCoefficient($vehicleTypeId);

        // Get period multiplier (КС - Seasonal coefficient)
        $periodMultiplier = $this->getPeriodMultiplier($period);

        // Get driver limit multiplier (КБО - Driver restriction coefficient)
        $driverMultiplier = $this->getDriverLimitMultiplier($driverLimit);

        // Calculate premium according to new 2026 regulations
        if ($driverLimit === 'unlimited') {
            // Unlimited drivers: ПР = СС х ТБ х КТ х КБО х КС / 100
            // For 12 months: КС = 1.0, so: ПР = СС х ТБ х КТ х КБО / 100
            $calculatedAmount = (self::INSURANCE_AMOUNT * $vehicleCoef * $regionCoef * $driverMultiplier * $periodMultiplier) / 100;
        } else {
            // Limited drivers: ПР = СС х ТБ х КТ х КБМ х КВ х КС х КН х КВЗ / 100
            // КВ, КН, КВЗ are always 1.0, so simplified: ПР = СС х ТБ х КТ х КБМ х КС / 100
            // For now, КБМ = 1.0 (no accidents) - can be extended later
            $accidentCoef = self::ACCIDENT_COEF_NO_ACCIDENTS;
            $calculatedAmount = (self::INSURANCE_AMOUNT * $vehicleCoef * $regionCoef * $accidentCoef * self::EXPERIENCE_COEF * $periodMultiplier * self::VIOLATION_COEF * self::AGE_COEF) / 100;
        }

        // Round and ensure minimum amount
        $amount = (int) round($calculatedAmount);

        // Fallback to default if calculation fails
        if ($amount <= 0 || !is_numeric($amount)) {
            $amount = self::DEFAULT_AMOUNT;
        }

        return [
            'amount' => $amount,
            'currency' => 'UZS',
            'calculation_details' => [
                'base_rate' => 150000, // document base rate used for reference
                'territory_coefficient' => $regionCoef,
                'period_coefficient' => $periodMultiplier,
                'driver_restriction_coefficient' => $driverMultiplier,
            ],
        ];
    }

    /**
     * Get region coefficient based on government number
     */
    private function getRegionCoefficient(string $govNumber): float
    {
        $prefix = substr(trim($govNumber), 0, 2);

        // Tashkent city (01) and Tashkent region (10) have higher coefficient
        return in_array($prefix, ['01', '10']) ? self::REGION_TASHKENT : self::REGION_OTHER;
    }

    /**
     * Get vehicle type coefficient
     */
    private function getVehicleTypeCoefficient(int $vehicleTypeId): float
    {
        // If type is 2, convert to 1 (as per original logic)
        if ($vehicleTypeId === 2) {
            $vehicleTypeId = 1;
        }

        return self::VEHICLE_TYPES[$vehicleTypeId] ?? self::VEHICLE_TYPES[2];
    }

    /**
     * Get period multiplier
     */
    private function getPeriodMultiplier(float|string $period): float
    {
        $period = (string) $period;
        return self::PERIOD_MULTIPLIERS[$period] ?? 1.0;
    }

    /**
     * Get driver limit multiplier
     */
    private function getDriverLimitMultiplier(string $driverLimit): int
    {
        return $driverLimit === 'limited' ? self::DRIVER_LIMITED : self::DRIVER_UNLIMITED;
    }

    /**
     * Validate calculation inputs
     */
    private function validateInputs(
        string $govNumber,
        int $vehicleTypeId,
        float|string $period,
        string $driverLimit
    ): void {
        if (empty($govNumber) || strlen($govNumber) < 2) {
            throw new \InvalidArgumentException('Invalid government number');
        }

        if ($vehicleTypeId < 1) {
            throw new \InvalidArgumentException('Invalid vehicle type ID');
        }

        $validPeriods = ['1', '0.7', '0.4'];
        if (!in_array((string) $period, $validPeriods)) {
            throw new \InvalidArgumentException('Invalid insurance period');
        }

        $validDriverLimits = ['limited', 'unlimited'];
        if (!in_array($driverLimit, $validDriverLimits)) {
            throw new \InvalidArgumentException('Invalid driver limit type');
        }
    }

    /**
     * Verify submitted price matches calculated price
     *
     * @param int $submittedAmount Amount submitted by client
     * @param string $govNumber Government number
     * @param int $vehicleTypeId Vehicle type ID
     * @param float|string $period Insurance period
     * @param string $driverLimit Driver limit type
     * @param int $tolerance Tolerance in UZS (default 100 UZS for rounding)
     * @return bool True if prices match within tolerance
     */
    public function verifyPrice(
        int $submittedAmount,
        string $govNumber,
        int $vehicleTypeId,
        float|string $period,
        string $driverLimit,
        int $tolerance = 100
    ): bool {
        $calculated = $this->calculate($govNumber, $vehicleTypeId, $period, $driverLimit);

        $difference = abs($calculated['amount'] - $submittedAmount);

        return $difference <= $tolerance;
    }
}
