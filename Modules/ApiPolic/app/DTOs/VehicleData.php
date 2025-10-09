<?php

namespace Modules\ApiPolic\DTOs;

final class VehicleData
{
    public function __construct(
        public ?string $brand = null,
        public ?string $model = null,
        public ?int $year = null,
        public ?string $vin = null,
        public ?string $license_plate = null,
        public ?string $color = null,
        public ?string $engine_type = null,
        public ?string $fuel_type = null,
        public ?string $transmission = null,
        public ?int $mileage = null,
        public ?string $status = null,
        public ?int $owner_id = null,
        public ?string $insurance_expires_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
            year: $data['year'] ?? null,
            vin: $data['vin'] ?? null,
            license_plate: $data['license_plate'] ?? null,
            color: $data['color'] ?? null,
            engine_type: $data['engine_type'] ?? null,
            fuel_type: $data['fuel_type'] ?? null,
            transmission: $data['transmission'] ?? null,
            mileage: $data['mileage'] ?? null,
            status: $data['status'] ?? null,
            owner_id: $data['owner_id'] ?? null,
            insurance_expires_at: $data['insurance_expires_at'] ?? null,
        );
    }
}
