<?php

namespace Modules\ApiPolic\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ApiPolic\Actions\CreateVehicleAction;
use Modules\ApiPolic\Actions\DeleteVehicleAction;
use Modules\ApiPolic\Actions\UpdateVehicleAction;
use Modules\ApiPolic\DTOs\VehicleData;
use Modules\ApiPolic\Http\Requests\IndexVehicleRequest;
use Modules\ApiPolic\Http\Requests\StoreVehicleRequest;
use Modules\ApiPolic\Http\Requests\UpdateVehicleRequest;
use Modules\ApiPolic\Http\Resources\VehicleCollection;
use Modules\ApiPolic\Http\Resources\VehicleResource;
use Modules\ApiPolic\Models\Vehicle;

class ApiVehicleController extends Controller
{
    public function __construct(
        private CreateVehicleAction $createVehicleAction,
        private UpdateVehicleAction $updateVehicleAction,
        private DeleteVehicleAction $deleteVehicleAction,
    ) {}

    /**
     * Display a listing of vehicles.
     */
    public function index(IndexVehicleRequest $request): VehicleCollection
    {
        $query = Vehicle::query()->with(['owner']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->validated('search');
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%");
            });
        }

        // Apply brand filter
        if ($request->filled('brand')) {
            $query->byBrand($request->validated('brand'));
        }

        // Apply year range filter
        if ($request->filled('year_from') || $request->filled('year_to')) {
            $yearFrom = $request->validated('year_from', 1900);
            $yearTo = $request->validated('year_to', date('Y'));
            $query->byYearRange($yearFrom, $yearTo);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->validated('status'));
        }

        $perPage = $request->validated('per_page', 15);
        $vehicles = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return new VehicleCollection($vehicles);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicleData = VehicleData::fromArray($request->validated());
        $vehicle = $this->createVehicleAction->execute($vehicleData);

        return response()->json([
            'message' => 'Vehicle created successfully',
            'data' => new VehicleResource($vehicle->load('owner')),
        ], 201);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle): VehicleResource
    {
        return new VehicleResource($vehicle->load('owner'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $vehicleData = VehicleData::fromArray($request->validated());
        $updatedVehicle = $this->updateVehicleAction->execute($vehicle, $vehicleData);

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'data' => new VehicleResource($updatedVehicle->load('owner')),
        ]);
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->deleteVehicleAction->execute($vehicle);

        return response()->json([
            'message' => 'Vehicle deleted successfully',
        ]);
    }
}
