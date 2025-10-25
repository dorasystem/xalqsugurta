# 🔍 Debugging Guide for Order System

## Xatolik Aniqlash Qadamlari

### 1. Session Ma'lumotlarini Tekshirish
```
http://127.0.0.1:8080/debug-session
```
Bu sahifa sizga quyidagilarni ko'rsatadi:
- Session'da qaysi ma'lumotlar bor
- Application data mavjudmi?
- API response mavjudmi?
- Barcha session data (JSON formatda)

### 2. Log Fayllarini Kuzatish
```bash
tail -f storage/logs/laravel.log
```

Yoki oxirgi 50 qatorni ko'rish:
```bash
tail -n 50 storage/logs/laravel.log
```

### 3. Keng Tarqalgan Muammolar

#### ❌ Muammo 1: "Ariza ma'lumotlari topilmadi"
**Sabab:** Session'da data saqlanmagan
**Yechim:**
1. `/debug-session` sahifasini oching
2. Agar `accident_application_data` yo'q bo'lsa:
   - Main page'dan qaytadan ariza to'ldiring
   - Application page'ga o'tgandan keyin `/debug-session` ni tekshiring

#### ❌ Muammo 2: Database connection error
**Sabab:** MySQL ishlamayapti yoki `.env` noto'g'ri
**Yechim:**
```bash
# Database connection tekshirish
php artisan migrate:status

# Agar xatolik bo'lsa, MySQL ishga tushiring:
sudo systemctl start mysql
# yoki Docker ishlatayotgan bo'lsangiz:
docker-compose up -d mysql
```

#### ❌ Muammo 3: "SQLSTATE[HY000] [2002]"
**Sabab:** Database'ga ulanish muammosi
**Yechim:**
1. `.env` faylni tekshiring:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

2. Migration'ni ishga tushiring:
```bash
php artisan migrate
```

#### ❌ Muammo 4: "Class 'App\Models\Order' not found"
**Sabab:** Composer autoload yangilanmagan
**Yechim:**
```bash
composer dump-autoload
```

#### ❌ Muammo 5: Session yo'qoladi
**Sabab:** Session driver noto'g'ri yoki session table yo'q
**Yechim:**
```bash
# Agar SESSION_DRIVER=database bo'lsa:
php artisan session:table
php artisan migrate

# Yoki file-based session ishlatish:
# .env da:
SESSION_DRIVER=file
```

### 4. Step-by-Step Testing

#### Test 1: Application Page'ga Kirish
1. `/uz/accident` ga o'ting
2. Formani to'ldiring
3. Submit qiling
4. Application page'ga o'tdingizmi? ✅

#### Test 2: Session Data Tekshirish
1. `/debug-session` ga o'ting
2. `accident_application_data` ko'rsatilganmi? ✅
3. Ma'lumotlar to'g'rimi? ✅

#### Test 3: Storage (Order Creation)
1. Application page'da "To'lovga O'tish" tugmasini bosing
2. Agar xatolik chiqsa:
   - Xatolik xabarini o'qing
   - Log'larni tekshiring: `tail -f storage/logs/laravel.log`
   - Debug session'ni tekshiring

#### Test 4: Payment Page
1. Order muvaffaqiyatli yaratilgandan keyin
2. Payment page'ga o'tishi kerak
3. Order ma'lumotlari ko'rsatilishi kerak ✅

### 5. Manual Testing Commands

```bash
# Cache tozalash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Session tozalash (test uchun)
php artisan optimize:clear

# Database tekshirish
php artisan db:show

# Migration status
php artisan migrate:status

# Tinker orqali test (interactive)
php artisan tinker
>>> session()->all()
>>> \App\Models\Order::count()
>>> \App\Models\Order::latest()->first()
```

### 6. Error Message Patterns

| Xatolik Xabari | Sabab | Yechim |
|----------------|-------|--------|
| "Ariza ma'lumotlari topilmadi" | Session bo'sh | Form'ni qaytadan to'ldiring |
| "SQLSTATE[HY000]" | DB connection | MySQL'ni ishga tushiring |
| "Class not found" | Autoload muammosi | `composer dump-autoload` |
| "419 Page Expired" | CSRF token muammosi | Page'ni refresh qiling |
| "Route not found" | Route cache muammosi | `php artisan route:clear` |

### 7. Useful Debugging Code

Application page'da qo'shish mumkin (test uchun):
```blade
{{-- Temporary debugging --}}
@if(config('app.debug'))
<div class="alert alert-info">
    <strong>Debug Info:</strong>
    <pre>{{ json_encode([
        'has_session' => session()->has('accident_application_data'),
        'session_id' => session()->getId(),
    ], JSON_PRETTY_PRINT) }}</pre>
</div>
@endif
```

### 8. Production Checklist

Muammoni hal qilgandan keyin:
- [ ] Debug route'ni o'chirish (`/debug-session`)
- [ ] Log level'ni pasaytirish (`.env`: `LOG_LEVEL=error`)
- [ ] `APP_DEBUG=false` qilish
- [ ] Debugging code'larni olib tashlash
- [ ] Cache'ni tozalash va build qilish

---

## 💡 Tez Yechim

Agar hech narsa ishlamasa:

```bash
# Hamma narsani tozalash
php artisan optimize:clear
composer dump-autoload
php artisan config:cache
php artisan route:cache

# Database setup
php artisan migrate:fresh

# Test qilish
php artisan serve
# Browser: http://127.0.0.1:8000/debug-session
```







