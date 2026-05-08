# Architecture

This project is organized as a Laravel API application plus a shared Docker infrastructure stack.

## High-Level View

```text
Client
  |
  | HTTP :8000
  v
project_api container
  |
  | Laravel 12 + Octane + FrankenPHP
  |
  +--> MySQL 8.4      shared_mysql:3306
  +--> Redis 7        shared_redis:6379
  +--> Mailpit        shared_mailpit:1025 / :8025
  +--> MinIO          shared_minio:9000 / :9001
```

The `project_api` service joins the external Docker network named `shared_network`. The shared services stack creates that network and attaches MySQL, Redis, Mailpit, and MinIO to it.

## Runtime Components

### Laravel API

Location: `docker-laravel/project-api`

The API is a Laravel 12 application. The Docker image is based on `dunglas/frankenphp:latest` and installs PHP extensions required by the app:

- `pdo_mysql`
- `redis`
- `pcntl`
- `bcmath`
- `intl`
- `zip`
- `opcache`

The container starts Laravel Octane with the FrankenPHP server:

```bash
php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000
```

### Shared Services

Location: `shared-services`

The shared services stack is reusable infrastructure for this and other local services.

| Service | Purpose |
| --- | --- |
| MySQL | Main relational database. |
| Redis | Cache, session, and queue backend. |
| Mailpit | Local email capture and inspection. |
| MinIO | Local S3-compatible object storage. |

## Network Topology

The application compose file expects an external network:

```yaml
networks:
  shared_network:
    external: true
```

The shared services compose file creates it:

```yaml
networks:
  shared_network:
    name: shared_network
```

Start `shared-services` before `project-api` so the network and infrastructure containers exist.

## Request Flow

1. A client sends an HTTP request to `localhost:8000`.
2. Docker forwards the request to the `project_api` container.
3. FrankenPHP receives the request and hands it to Laravel Octane.
4. Laravel routes the request through `routes/web.php` or `routes/api.php`.
5. Route closures query Eloquent models or Laravel services.
6. The application reads from MySQL and optionally uses Redis for caching.
7. Laravel returns a plain text or JSON response.

## Routes

### Web Routes

`routes/web.php`

| Method | Path | Behavior |
| --- | --- | --- |
| GET | `/` | Returns Laravel's `welcome` view. |

### API Routes

`routes/api.php`

| Method | Path | Behavior |
| --- | --- | --- |
| GET | `/api/products` | Reads the first 5 products from MySQL. |
| GET | `/api/plain-ok` | Returns `OK`. |
| GET | `/api/json-ok` | Returns `{ "ok": true }`. |
| GET | `/api/redis` | Caches the first 5 products in Redis for 60 seconds. |
| GET | `/api/user` | Returns the authenticated Sanctum user. |

## Data Model

### users

Created by Laravel's default user migration.

The `App\Models\User` model supports:

- `name`
- `email`
- `password`

Hidden fields:

- `password`
- `remember_token`

Casts:

- `email_verified_at` as datetime
- `password` as hashed

### products

Created by `2026_05_08_052104_create_products_table.php`.

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint | Primary key. |
| `name` | string | Product name. |
| `price` | decimal(10,2) | Product price. |
| `created_at` | timestamp | Managed by Laravel. |
| `updated_at` | timestamp | Managed by Laravel. |

`ProductSeeder` creates 10,000 sample products named `Product 1` through `Product 10000` with random prices from 100 to 1000.

## Configuration

The current local `.env` configures:

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

Do not commit real application secrets. Generate a new app key for each deployed environment.

## Caching

The `/api/redis` route uses:

```php
Cache::remember('products', 60, function () {
    return Product::select('id', 'name', 'price')->limit(5)->get();
});
```

The cache key is `products`, and the cache duration is 60 seconds.

## Authentication

Laravel Sanctum is installed and the default `/api/user` route is protected by `auth:sanctum`.

The project currently does not define custom login, token creation, or registration endpoints. Those would need to be added before external clients can obtain Sanctum API tokens through this API.

## Storage and Email

MinIO and Mailpit are available in the shared stack, but the application currently uses Laravel's default local filesystem and log mailer settings unless environment variables are changed.

To use Mailpit from inside Docker, configure:

```text
MAIL_MAILER=smtp
MAIL_HOST=shared_mailpit
MAIL_PORT=1025
```

To use MinIO as S3-compatible storage, configure the Laravel S3 disk with the MinIO endpoint and credentials from `shared-services/docker-compose.yml`.

## Operational Notes

- Start `shared-services` before `project-api`.
- The API app depends on the external `shared_network` Docker network.
- Host MySQL access is exposed on port `3307`; containers use `shared_mysql:3306`.
- Redis is exposed on host port `6379`; containers use `shared_redis:6379`.
- The API is exposed on host port `8000`.
- The project includes starter PHPUnit example tests only.
