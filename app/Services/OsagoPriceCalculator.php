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
    private const INSURANCE_AMOUNT = 40000000;

    // Default premium if calculation fails
    private const DEFAULT_AMOUNT = 168000;

    // Region coefficients
    private const REGION_TASHKENT = 1.4;
    private const REGION_OTHER = 1.2;

    // Vehicle type coefficients
    private const VEHICLE_TYPES = [
        1 => 0.1,   // Passenger cars
        2 => 0.1,   // Passenger cars
        6 => 0.12,  // Cargo vehicles
        9 => 0.12,  // Buses
        15 => 0.04, // Motorcycles
    ];

    // Period multipliers
    private const PERIOD_MULTIPLIERS = [
        '1' => 1.0,    // 12 months
        '0.7' => 0.7,  // 6 months
        '0.4' => 0.4,  // 3 months
    ];

    // Driver limit multipliers
    private const DRIVER_UNLIMITED = 3;
    private const DRIVER_LIMITED = 1;

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

        // Get period multiplier
        $periodMultiplier = $this->getPeriodMultiplier($period);

        // Get driver limit multiplier
        $driverMultiplier = $this->getDriverLimitMultiplier($driverLimit);

        // Calculate premium: (vehicleCoef * regionCoef * periodMultiplier * driverMultiplier * INSURANCE_AMOUNT) / 100
        $calculatedAmount = ($vehicleCoef * $regionCoef * $periodMultiplier * $driverMultiplier * self::INSURANCE_AMOUNT) / 100;

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
