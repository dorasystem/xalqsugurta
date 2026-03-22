# GitHub CI/CD — Deploy qilish bo'yicha qo'llanma

## Umumiy ko'rinish

```
git push → GitHub Actions ishga tushadi
              │
              ├─ 1-bosqich: Docker image yig'iladi va ghcr.io ga yuklanadi
              │
              └─ 2-bosqich: Serverga SSH orqali deploy qilinadi
                    ├─ git pull (yangi kod)
                    ├─ docker pull (yangi image)
                    ├─ docker-compose up (konteynerlar yangilanadi)
                    └─ php artisan migrate / config:cache / route:cache / view:cache
```

---

## Fayl strukturasi

```
.github/
  workflows/
    deploy.yml          ← Asosiy CI/CD workflow
docker-compose.yml      ← Lokal ishlab chiqish uchun
docker-compose.prod.yml ← Production uchun (CI/CD ishlatadi)
Dockerfile              ← PHP 8.2 FPM + Composer + Node image
```

---

## GitHub Secrets (Repo → Settings → Secrets → Actions)

| Secret          | Nima?                                            | Misol                        |
|-----------------|--------------------------------------------------|------------------------------|
| `SERVER_IP`     | Serverning IP manzili                            | `185.123.45.67`              |
| `SERVER_USER`   | SSH foydalanuvchi nomi                           | `ubuntu` yoki `root`         |
| `SSH_KEY`       | Server uchun private SSH kalit (to'liq matn)     | `-----BEGIN OPENSSH...`      |
| `REGISTRY_TOKEN`| GitHub Personal Access Token (PAT)              | `ghp_xxxxxxxxxxxx`           |

### REGISTRY_TOKEN ni qanday olish?

1. GitHub → **Settings** → **Developer settings** → **Personal access tokens** → **Tokens (classic)**
2. **Generate new token** bosing
3. Permissions: `write:packages`, `read:packages`, `delete:packages` belgilang
4. Tokenni nusxalab, GitHub Secrets ga qo'shing

---

## Serverda birinchi marta sozlash

Serverda faqat **bir marta** qilinadi:

```bash
# 1. SSH bilan serverga kirish
ssh ubuntu@YOUR_SERVER_IP

# 2. Docker o'rnatish (agar yo'q bo'lsa)
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# 3. Docker Compose o'rnatish (agar yo'q bo'lsa)
sudo apt install docker-compose -y

# 4. Loyihani clone qilish
sudo mkdir -p /var/www
cd /var/www
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git xalqsugurta
cd xalqsugurta

# 5. .env faylini sozlash
cp .env.example .env
nano .env   # kerakli o'zgaruvchilarni to'ldiring

# 6. MySQL va Redis ni ishga tushirish (birinchi marta)
docker-compose -f docker-compose.prod.yml up -d mysql redis

# 7. APP_KEY generatsiya
docker-compose -f docker-compose.prod.yml run --rm app php artisan key:generate

# 8. Barcha konteynerlarni ishga tushirish
docker-compose -f docker-compose.prod.yml up -d
```

---

## CI/CD qanday ishlaydi

### 1-bosqich — Docker image yig'ish (`build` job)

| Qadam                        | Nima qiladi?                                               |
|------------------------------|------------------------------------------------------------|
| `checkout`                   | Repository kodini yuklab oladi                             |
| `setup-buildx`               | Tezroq build uchun Docker Buildx yoqadi                    |
| `login to ghcr.io`           | `REGISTRY_TOKEN` bilan GitHub Container Registry ga kiradi |
| `metadata`                   | Image taglarini yaratadi: `latest` va `sha-xxxxxxx`        |
| `build-push`                 | `Dockerfile` dan image yig'adi va `ghcr.io` ga yuklaydi    |

Image manzili: `ghcr.io/YOUR_USERNAME/YOUR_REPO:latest`

GitHub Actions cache ishlatilgani uchun ikkinchi build **2-3x tezroq** bo'ladi.

### 2-bosqich — Server deploy (`deploy` job)

| Qadam                        | Nima qiladi?                                                      |
|------------------------------|-------------------------------------------------------------------|
| `git pull origin main`       | Serverga yangi konfiguratsiya/view fayllarni tortib oladi         |
| `docker login ghcr.io`       | Server ham registry ga kiradi                                     |
| `docker pull`                | Yangi yig'ilgan image ni tortib oladi                             |
| `docker-compose up --no-build` | Faqat `app` va `nginx` konteynerlarini yangilaydi (DB tegmaydi) |
| `php artisan migrate`        | Yangi migratsiyalarni ishga tushiradi                             |
| `php artisan *:cache`        | Config, route, view keshlarini yangilaydi                         |
| `docker image prune`         | Eski image larni o'chirib disk bo'shatadi                         |

**Zero-downtime deploy:** `--no-deps` flag bilan faqat `app` va `nginx` qayta ishga tushiriladi — MySQL va Redis to'xtatilmaydi.

---

## docker-compose.prod.yml — lokal va CI/CD farqi

```bash
# Lokal production test (image'ni o'zi yig'adi):
docker-compose -f docker-compose.prod.yml up --build

# CI/CD (tayyor image'ni ghcr.io dan tortadi):
APP_IMAGE=ghcr.io/username/repo:latest \
  docker-compose -f docker-compose.prod.yml up -d --no-build
```

`${APP_IMAGE:-impex-insurance-app:prod}` — agar `APP_IMAGE` o'rnatilmagan bo'lsa, default lokal nom ishlatiladi.

---

## Workflow ishga tushirish

```bash
# main branchga push qilish — avtomatik deploy boshlaydi
git add .
git commit -m "feat: yangi xususiyat"
git push origin main
```

Deploy holatini ko'rish: **GitHub → Actions** tab.

---

## Muammolarni hal qilish

### Deploy muvaffaqiyatsiz bo'lsa

```bash
# Serverda loglarni ko'rish
ssh ubuntu@SERVER_IP
cd /var/www/xalqsugurta
docker-compose -f docker-compose.prod.yml logs app --tail=50
```

### Migration xatosi

```bash
docker-compose -f docker-compose.prod.yml exec app php artisan migrate:status
```

### Image yuklanmasa (403 xatosi)

`REGISTRY_TOKEN` da `write:packages` va `read:packages` permission borligini tekshiring.

### SSH ulanmasa

```bash
# Lokal mashinada test qiling
ssh -i your_key USER@SERVER_IP
```

---

## Portlar

| Servis         | Port   | Maqsad               |
|----------------|--------|----------------------|
| Nginx (prod)   | `8080` | Asosiy web server    |
| MySQL          | ichki  | Tashqaridan yopiq    |
| Redis          | ichki  | Tashqaridan yopiq    |

Nginx `8080` portida ishlaydi. Agar domen bilan ishlasangiz, uni reverse proxy (nginx/caddy) orqali `443 → 8080` ga yo'naltiring.
