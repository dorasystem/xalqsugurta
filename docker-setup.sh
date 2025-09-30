#!/bin/bash

echo "🚀 Setting up Impex Insurance Docker Environment..."

# Create necessary directories
mkdir -p docker/nginx
mkdir -p docker/php
mkdir -p docker/mysql

# Set permissions
chmod +x docker-setup.sh

# Generate application key if not exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env 2>/dev/null || echo "Creating new .env file"
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating application key..."
    docker run --rm -v $(pwd):/app -w /app php:8.2-cli php -r "echo 'APP_KEY=' . base64_encode(random_bytes(32)) . PHP_EOL;" >> .env
fi

# Build and start containers
echo "🐳 Building and starting Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 30

# Run Laravel commands
echo "📦 Installing dependencies and setting up Laravel..."
docker-compose exec app composer install --no-interaction
docker-compose exec app php artisan key:generate --force
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Run migrations
echo "🗄️ Running database migrations..."
docker-compose exec app php artisan migrate --force

# Seed database if seeders exist
echo "🌱 Seeding database..."
docker-compose exec app php artisan db:seed --force

# Set storage permissions
echo "🔧 Setting storage permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage

echo "✅ Setup complete!"
echo ""
echo "🌐 Application: http://localhost:8080"
echo "🗄️ phpMyAdmin: http://localhost:8081"
echo "🔴 Redis Commander: http://localhost:8082"
echo ""
echo "📋 Useful commands:"
echo "  docker-compose up -d          # Start containers"
echo "  docker-compose down           # Stop containers"
echo "  docker-compose logs -f app    # View app logs"
echo "  docker-compose exec app bash  # Access app container"
