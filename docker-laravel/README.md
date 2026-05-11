# Project API

Project API is a Laravel 12 application served by Laravel Octane on FrankenPHP. It exposes simple product and health-check API endpoints and connects to shared Docker services for MySQL and Redis.

## Application Structure

```text
project-api/
├── app/
│   └── Models/
│       ├── Product.php
│       └── User.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── ProductSeeder.php
├── routes/
│   ├── api.php
│   └── web.php
├── Dockerfile
├── docker-compose.yml
├── composer.json
└── package.json
```

## Requirements

- Docker and Docker Compose
- Shared Docker network named `shared_network`
- MySQL service reachable as `shared_mysql`
- Redis service reachable as `shared_redis`

The root repository contains a `shared-services` compose stack that provides these services.

## Environment

The local `.env` uses MySQL and Redis from the shared Docker stack:

```text
DB_CONNECTION=mysql
DB_HOST=shared_mysql
DB_PORT=3306
DB_DATABASE=project_api
DB_USERNAME=root
DB_PASSWORD=root

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=shared_redis
REDIS_PORT=6379

OCTANE_SERVER=frankenphp
```

Keep `.env` local. Use `.env.example` as the template for new environments.

## Running With Docker

From the repository root:

```bash
cd shared-services
docker compose up -d
```

Then start the API:

```bash
cd ../docker-laravel/project-api
docker compose up -d --build
```

The app is available at:

```text
http://localhost:8000
```

## Database

Create the `project_api` database in the shared MySQL container if needed:

```bash
docker exec -it shared_mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS project_api;"
```

Run migrations:

```bash
docker exec -it project_api php artisan migrate
```

Seed the default user:

```bash
docker exec -it project_api php artisan db:seed
```

Seed product data:

```bash
docker exec -it project_api php artisan db:seed --class=ProductSeeder
```

## API Reference

Laravel automatically prefixes routes in `routes/api.php` with `/api`.

### `GET /api/plain-ok`

Returns a plain text health-check response.

Response:

```text
OK
```

### `GET /api/json-ok`

Returns a JSON health-check response.

Response:

```json
{
  "ok": true
}
```

### `GET /api/products`

Returns the first 5 products from the database.

Response shape:

```json
[
  {
    "id": 1,
    "name": "Product 1",
    "price": "100.00"
  }
]
```

### `GET /api/redis`

Returns the first 5 products and caches them in Redis for 60 seconds using the cache key `products`.

Response shape:

```json
[
  {
    "id": 1,
    "name": "Product 1",
    "price": "100.00"
  }
]
```

### `GET /api/user`

Returns the authenticated user.

Authentication:

```text
auth:sanctum
```

This route requires a valid Laravel Sanctum-authenticated request. The project currently includes Sanctum but does not define custom token issuing endpoints.

## Models

### Product

`App\Models\Product`

Backed by the `products` table:

- `id`
- `name`
- `price`
- `created_at`
- `updated_at`

### User

`App\Models\User`

Backed by the `users` table and configured with Laravel's default authentication model behavior.

## Development Commands

```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
npm install

# Build frontend assets
npm run build

# Run Laravel tests
php artisan test

# Run all configured Composer test commands
composer test

# Clear application caches
php artisan optimize:clear
```

Inside Docker, prefix Artisan commands with:

```bash
docker exec -it project_api
```

Example:

```bash
docker exec -it project_api php artisan optimize:clear
```

## Docker Image

The Dockerfile:

1. Uses `dunglas/frankenphp:latest`.
2. Installs PHP extensions for MySQL, Redis, Octane, math, internationalization, compression, and opcode caching.
3. Copies Composer from the official Composer image.
4. Installs PHP dependencies.
5. Starts Octane with FrankenPHP on port `8000`.

## Testing

The repository currently contains Laravel's starter example tests:

- `tests/Feature/ExampleTest.php`
- `tests/Unit/ExampleTest.php`

Run them with:

```bash
docker exec -it project_api php artisan test
```
