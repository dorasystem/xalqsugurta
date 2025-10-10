# 🚀 Vehicle API - Quick Start (5 Minutes)

## ⚡ Super Quick Setup

### 1. Run Migration (30 seconds)
```bash
php artisan migrate
```

### 2. Start Server (10 seconds)
```bash
php artisan serve
```

### 3. Test API (2 minutes)

**Create a test user first** (if you don't have one):
```bash
php artisan tinker
>>> \App\Models\User::create(['name' => 'John Doe', 'email' => 'john@test.com', 'password' => bcrypt('password')]);
>>> exit
```

**Test the API:**

```bash
# 1. List vehicles (should be empty initially)
curl http://localhost:8000/api/v1/vehicles

# 2. Create a vehicle
curl -X POST http://localhost:8000/api/v1/vehicles \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "brand": "Toyota",
    "model": "Camry",
    "year": 2023,
    "vin": "1HGBH41JXMN109186",
    "license_plate": "ABC123",
    "color": "White",
    "engine_type": "2.5L",
    "fuel_type": "gasoline",
    "transmission": "automatic",
    "mileage": 15000,
    "owner_id": 1
  }'

# 3. List vehicles again (should see your vehicle)
curl http://localhost:8000/api/v1/vehicles

# 4. Get specific vehicle
curl http://localhost:8000/api/v1/vehicles/1

# 5. Update vehicle
curl -X PUT http://localhost:8000/api/v1/vehicles/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"mileage": 16000}'

# 6. Search vehicles
curl "http://localhost:8000/api/v1/vehicles?search=Toyota"

# 7. Filter by year
curl "http://localhost:8000/api/v1/vehicles?year_from=2020&year_to=2024"

# 8. Delete vehicle (optional)
curl -X DELETE http://localhost:8000/api/v1/vehicles/1 \
  -H "Accept: application/json"
```

## 🎯 Available Endpoints

```
GET     /api/v1/vehicles              - List all vehicles
POST    /api/v1/vehicles              - Create vehicle
GET     /api/v1/vehicles/{id}         - Get single vehicle
PUT     /api/v1/vehicles/{id}         - Update vehicle
DELETE  /api/v1/vehicles/{id}         - Delete vehicle
```

## 📦 Using Postman

1. Import `Vehicle_API.postman_collection.json`
2. Update `base_url` to `http://localhost:8000`
3. Start testing!

## 🔍 Verify Routes

```bash
php artisan route:list --path=api
```

## 📖 Full Documentation

- **Complete API Docs**: `API_DOCUMENTATION.md`
- **Detailed Setup**: `SETUP.md`
- **Implementation Summary**: `SUMMARY.md`

## ✅ Done!

Your Vehicle API is ready! 🎉

**Next Steps:**
1. Add authentication (see SETUP.md)
2. Add tests
3. Deploy to production

---

**Need help?** Check the documentation files or Laravel logs:
```bash
tail -f storage/logs/laravel.log
```





