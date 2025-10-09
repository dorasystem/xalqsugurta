# ApiPolic Module - Vehicle Management API

## 📋 Overview
A complete RESTful API module for vehicle management with CRUD operations, advanced filtering, search, and pagination.

## 🚀 Quick Start

### 1. Install Dependencies
```bash
composer dump-autoload
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Test the API
```bash
# List all vehicles
curl http://localhost:8000/api/v1/vehicles

# Create a vehicle
curl -X POST http://localhost:8000/api/v1/vehicles \
  -H "Content-Type: application/json" \
  -d '{"brand":"Toyota","model":"Camry","year":2023,"vin":"1HGBH41JXMN109186","license_plate":"ABC123","color":"White","engine_type":"2.5L","fuel_type":"gasoline","transmission":"automatic","mileage":15000,"owner_id":1}'
```

## 📁 Module Structure

```
ApiPolic/
├── app/
│   ├── Actions/              # Business logic (CreateVehicleAction, UpdateVehicleAction, DeleteVehicleAction)
│   ├── DTOs/                 # Data Transfer Objects (VehicleData)
│   ├── Http/
│   │   ├── Controllers/      # ApiVehicleController
│   │   ├── Requests/         # Validation (IndexVehicleRequest, StoreVehicleRequest, UpdateVehicleRequest)
│   │   └── Resources/        # API responses (VehicleResource, VehicleCollection)
│   └── Models/               # Vehicle model
├── database/
│   └── migrations/           # Database schema
├── routes/
│   └── api/api.php          # API routes
├── API_DOCUMENTATION.md      # Complete API documentation
└── README.md                 # This file
```

## 🎯 Features

### ✅ Complete CRUD Operations
- ✅ List vehicles with pagination
- ✅ Create new vehicle
- ✅ View single vehicle
- ✅ Update vehicle
- ✅ Delete vehicle

### 🔍 Advanced Features
- ✅ **Search**: Search across brand, model, license plate, VIN
- ✅ **Filtering**: Filter by brand, year range, status
- ✅ **Pagination**: Configurable per page (max 100)
- ✅ **Eager Loading**: Prevent N+1 queries
- ✅ **Validation**: Complete input validation
- ✅ **Error Handling**: Proper error responses
- ✅ **Type Safety**: Full PHP 8.2+ type hints

### 🏗️ Architecture
- ✅ **Thin Controllers**: Only routing logic
- ✅ **Actions**: Single-responsibility business logic
- ✅ **DTOs**: Clean data transfer
- ✅ **FormRequests**: Centralized validation
- ✅ **Resources**: Consistent API responses
- ✅ **SOLID Principles**: Clean, maintainable code

## 📚 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/vehicles` | List all vehicles |
| POST | `/api/v1/vehicles` | Create new vehicle |
| GET | `/api/v1/vehicles/{id}` | Get single vehicle |
| PUT/PATCH | `/api/v1/vehicles/{id}` | Update vehicle |
| DELETE | `/api/v1/vehicles/{id}` | Delete vehicle |

## 🔧 Query Parameters

**List Vehicles:**
- `search` - Search text
- `brand` - Filter by brand
- `year_from` - Minimum year
- `year_to` - Maximum year
- `status` - Filter by status (active, inactive, sold)
- `per_page` - Items per page (1-100)
- `page` - Page number

**Example:**
```
GET /api/v1/vehicles?search=Toyota&year_from=2020&status=active&per_page=20
```

## 📖 Full Documentation

See [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for:
- Complete endpoint specifications
- Request/response examples
- Validation rules
- Error responses
- Database schema
- Performance considerations

## 🛠️ Tech Stack

- **Laravel 12+**
- **PHP 8.2+**
- **RESTful API**
- **Eloquent ORM**
- **Form Requests**
- **API Resources**

## 🎨 Code Quality

- ✅ PSR-12 coding standards
- ✅ Single Responsibility Principle
- ✅ Dependency Injection
- ✅ Type hints everywhere
- ✅ Clean, readable code

## 📝 Vehicle Fields

- Brand, Model, Year
- VIN (17 chars, unique)
- License Plate (unique)
- Color, Engine Type
- Fuel Type (gasoline, diesel, electric, hybrid)
- Transmission (manual, automatic, cvt)
- Mileage, Status
- Owner (relationship with User)
- Insurance Expiration Date

## 🚦 Status

✅ **Production Ready**

All core features implemented and tested.

## 📄 License

MIT License

