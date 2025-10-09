# Vehicle API Documentation

## Overview
RESTful API for managing vehicles with complete CRUD operations, search, and filtering capabilities.

## Base URL
```
/api/v1
```

## Endpoints

### 1. Get All Vehicles (List)
**Endpoint:** `GET /api/v1/vehicles`

**Query Parameters:**
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `search` | string | Search in brand, model, license_plate, vin | `?search=Toyota` |
| `brand` | string | Filter by brand | `?brand=Toyota` |
| `year_from` | integer | Minimum year | `?year_from=2020` |
| `year_to` | integer | Maximum year | `?year_to=2024` |
| `status` | string | Filter by status (active, inactive, sold) | `?status=active` |
| `per_page` | integer | Items per page (max 100) | `?per_page=20` |
| `page` | integer | Page number | `?page=1` |

**Example Request:**
```bash
curl -X GET "http://your-domain.com/api/v1/vehicles?search=Toyota&year_from=2020&per_page=20"
```

**Example Response:**
```json
{
    "data": [
        {
            "id": 1,
            "brand": "Toyota",
            "model": "Camry",
            "year": 2023,
            "vin": "1HGBH41JXMN109186",
            "license_plate": "ABC123",
            "color": "White",
            "engine_type": "2.5L 4-Cylinder",
            "fuel_type": "gasoline",
            "transmission": "automatic",
            "mileage": 15000,
            "status": "active",
            "insurance_expires_at": "2025-12-31",
            "owner": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "created_at": "2024-01-01 12:00:00",
            "updated_at": "2024-01-01 12:00:00"
        }
    ],
    "meta": {
        "total": 100,
        "count": 20,
        "per_page": 20,
        "current_page": 1,
        "total_pages": 5
    },
    "links": {
        "first": "http://your-domain.com/api/v1/vehicles?page=1",
        "last": "http://your-domain.com/api/v1/vehicles?page=5",
        "prev": null,
        "next": "http://your-domain.com/api/v1/vehicles?page=2"
    }
}
```

---

### 2. Create Vehicle
**Endpoint:** `POST /api/v1/vehicles`

**Request Body:**
```json
{
    "brand": "Toyota",
    "model": "Camry",
    "year": 2023,
    "vin": "1HGBH41JXMN109186",
    "license_plate": "ABC123",
    "color": "White",
    "engine_type": "2.5L 4-Cylinder",
    "fuel_type": "gasoline",
    "transmission": "automatic",
    "mileage": 15000,
    "owner_id": 1,
    "status": "active",
    "insurance_expires_at": "2025-12-31"
}
```

**Validation Rules:**
- `brand`: required, string, max 100 chars
- `model`: required, string, max 100 chars
- `year`: required, integer, min 1900, max current year
- `vin`: required, string, 17 chars, unique
- `license_plate`: required, string, max 20 chars, unique
- `color`: required, string, max 50 chars
- `engine_type`: required, string, max 50 chars
- `fuel_type`: required, enum (gasoline, diesel, electric, hybrid)
- `transmission`: required, enum (manual, automatic, cvt)
- `mileage`: required, integer, min 0
- `owner_id`: required, exists in users table
- `status`: optional, enum (active, inactive, sold)
- `insurance_expires_at`: optional, date, must be future date

**Example Request:**
```bash
curl -X POST "http://your-domain.com/api/v1/vehicles" \
  -H "Content-Type: application/json" \
  -d '{
    "brand": "Toyota",
    "model": "Camry",
    "year": 2023,
    "vin": "1HGBH41JXMN109186",
    "license_plate": "ABC123",
    "color": "White",
    "engine_type": "2.5L 4-Cylinder",
    "fuel_type": "gasoline",
    "transmission": "automatic",
    "mileage": 15000,
    "owner_id": 1
}'
```

**Success Response (201 Created):**
```json
{
    "message": "Vehicle created successfully",
    "data": {
        "id": 1,
        "brand": "Toyota",
        "model": "Camry",
        "year": 2023,
        "vin": "1HGBH41JXMN109186",
        "license_plate": "ABC123",
        "color": "White",
        "engine_type": "2.5L 4-Cylinder",
        "fuel_type": "gasoline",
        "transmission": "automatic",
        "mileage": 15000,
        "status": "active",
        "insurance_expires_at": null,
        "owner": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
    }
}
```

---

### 3. Get Single Vehicle
**Endpoint:** `GET /api/v1/vehicles/{id}`

**Example Request:**
```bash
curl -X GET "http://your-domain.com/api/v1/vehicles/1"
```

**Success Response (200 OK):**
```json
{
    "id": 1,
    "brand": "Toyota",
    "model": "Camry",
    "year": 2023,
    "vin": "1HGBH41JXMN109186",
    "license_plate": "ABC123",
    "color": "White",
    "engine_type": "2.5L 4-Cylinder",
    "fuel_type": "gasoline",
    "transmission": "automatic",
    "mileage": 15000,
    "status": "active",
    "insurance_expires_at": "2025-12-31",
    "owner": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "created_at": "2024-01-01 12:00:00",
    "updated_at": "2024-01-01 12:00:00"
}
```

---

### 4. Update Vehicle
**Endpoint:** `PUT/PATCH /api/v1/vehicles/{id}`

**Request Body (all fields optional):**
```json
{
    "brand": "Toyota",
    "model": "Camry XLE",
    "mileage": 16500,
    "status": "active",
    "insurance_expires_at": "2026-01-01"
}
```

**Example Request:**
```bash
curl -X PUT "http://your-domain.com/api/v1/vehicles/1" \
  -H "Content-Type: application/json" \
  -d '{
    "mileage": 16500,
    "status": "active"
}'
```

**Success Response (200 OK):**
```json
{
    "message": "Vehicle updated successfully",
    "data": {
        "id": 1,
        "brand": "Toyota",
        "model": "Camry XLE",
        "year": 2023,
        "vin": "1HGBH41JXMN109186",
        "license_plate": "ABC123",
        "color": "White",
        "engine_type": "2.5L 4-Cylinder",
        "fuel_type": "gasoline",
        "transmission": "automatic",
        "mileage": 16500,
        "status": "active",
        "insurance_expires_at": "2026-01-01",
        "owner": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-02 10:30:00"
    }
}
```

---

### 5. Delete Vehicle
**Endpoint:** `DELETE /api/v1/vehicles/{id}`

**Example Request:**
```bash
curl -X DELETE "http://your-domain.com/api/v1/vehicles/1"
```

**Success Response (200 OK):**
```json
{
    "message": "Vehicle deleted successfully"
}
```

---

## Error Responses

### Validation Error (422 Unprocessable Entity)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "vin": [
            "The vin must be 17 characters.",
            "The vin has already been taken."
        ],
        "year": [
            "The year must not be greater than 2025."
        ]
    }
}
```

### Not Found (404)
```json
{
    "message": "No query results for model [Modules\\ApiPolic\\Models\\Vehicle] 999"
}
```

### Server Error (500)
```json
{
    "message": "Server Error"
}
```

---

## Setup Instructions

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Test the API
Use Postman, Insomnia, or curl to test the endpoints.

### 3. Route List
To see all available routes:
```bash
php artisan route:list --path=api/v1
```

---

## Architecture

The API follows Laravel best practices:

### 🏗️ **Structure**
```
Modules/ApiPolic/
├── app/
│   ├── Actions/              # Business logic
│   │   ├── CreateVehicleAction.php
│   │   ├── UpdateVehicleAction.php
│   │   └── DeleteVehicleAction.php
│   ├── DTOs/                 # Data Transfer Objects
│   │   └── VehicleData.php
│   ├── Http/
│   │   ├── Controllers/      # Thin controllers
│   │   │   └── ApiVehicleController.php
│   │   ├── Requests/         # Validation
│   │   │   ├── IndexVehicleRequest.php
│   │   │   ├── StoreVehicleRequest.php
│   │   │   └── UpdateVehicleRequest.php
│   │   └── Resources/        # Response formatting
│   │       ├── VehicleResource.php
│   │       └── VehicleCollection.php
│   └── Models/
│       └── Vehicle.php
├── database/
│   └── migrations/
│       └── 2024_01_01_000000_create_vehicles_table.php
└── routes/
    └── api/
        └── api.php
```

### ✅ **Design Principles**
- **Thin Controllers**: Only routing logic
- **Actions**: Single-responsibility use cases
- **DTOs**: Clean data flow
- **FormRequests**: Centralized validation
- **Resources**: Consistent API responses
- **Eager Loading**: Prevent N+1 queries
- **Type Safety**: Full PHP 8.2+ type hints

---

## Database Schema

### vehicles table
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PRIMARY KEY |
| brand | varchar(100) | NOT NULL |
| model | varchar(100) | NOT NULL |
| year | integer | NOT NULL |
| vin | varchar(17) | NOT NULL, UNIQUE |
| license_plate | varchar(20) | NOT NULL, UNIQUE |
| color | varchar(50) | NOT NULL |
| engine_type | varchar(50) | NOT NULL |
| fuel_type | enum | gasoline, diesel, electric, hybrid |
| transmission | enum | manual, automatic, cvt |
| mileage | integer | DEFAULT 0 |
| status | enum | active, inactive, sold (DEFAULT: active) |
| owner_id | bigint | FOREIGN KEY (users.id) |
| insurance_expires_at | date | NULLABLE |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- Primary: `id`
- Unique: `vin`, `license_plate`
- Index: `brand, model`, `year`, `status`, `owner_id`

---

## Performance Considerations

1. **Eager Loading**: Owner relationship is loaded with `->with(['owner'])`
2. **Pagination**: Default 15 items per page, max 100
3. **Indexes**: Added on frequently queried columns
4. **Scopes**: Reusable query scopes in the model

---

## Security

1. **Mass Assignment Protection**: Using `$fillable` array
2. **Validation**: All inputs validated via FormRequests
3. **SQL Injection**: Protected by Eloquent ORM
4. **Unique Constraints**: VIN and license_plate must be unique

---

## Future Enhancements

- [ ] Add authentication (Sanctum)
- [ ] Add authorization policies
- [ ] Add rate limiting
- [ ] Add API versioning support
- [ ] Add filtering by owner
- [ ] Add bulk operations
- [ ] Add vehicle history tracking
- [ ] Add file uploads for vehicle images
- [ ] Add API documentation (OpenAPI/Swagger)
- [ ] Add caching for frequently accessed vehicles

