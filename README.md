# Docker Laravel Project

This repository contains a Dockerized Laravel API project and a reusable shared-services stack for local development.

The main application lives in `docker-laravel/project-api`. It is a Laravel 12 API running on PHP 8.2+ with Laravel Octane and FrankenPHP. The shared stack provides MySQL, Redis, Mailpit, and MinIO on a common Docker network.

## Repository Layout

```text
.
├── docker-laravel/
│   └── project-api/          # Laravel API application
│       ├── app/              # Application models and providers
│       ├── config/           # Laravel configuration
│       ├── database/         # Migrations, factories, and seeders
│       ├── routes/           # API, web, and console routes
│       ├── Dockerfile        # FrankenPHP application image
│       └── docker-compose.yml
├── shared-services/          # Reusable local infrastructure
│   ├── docker-compose.yml    # MySQL, Redis, Mailpit, MinIO
│   ├── Scriptsstart.sh
│   ├── Scriptsstop.sh
│   └── Scriptsrestart.sh
└── ARCHITECTURE.md
```

## Stack

- PHP 8.2+
- Laravel 12
- Laravel Octane
- FrankenPHP
- MySQL 8.4
- Redis 7
- Mailpit
- MinIO
- Vite and Tailwind CSS tooling

## Prerequisites

- Docker and Docker Compose
- Composer, if running Laravel commands directly on the host
- Node.js and npm, if running Vite directly on the host

## Local Setup

Start the shared infrastructure first:

```bash
cd shared-services
docker compose up -d
```

Start the Laravel API:

```bash
cd ../docker-laravel/project-api
docker compose up -d --build
```

The API container exposes the application at:

```text
http://localhost:8000
```

## Database Setup

The application is configured to connect to the shared MySQL container:

```text
DB_HOST=shared_mysql
DB_PORT=3306
DB_DATABASE=project_api
DB_USERNAME=root
DB_PASSWORD=root
```

Create the `project_api` database in MySQL if it does not already exist, then run migrations:

```bash
docker exec -it project_api php artisan migrate
```

To seed the default test user:

```bash
docker exec -it project_api php artisan db:seed
```

To seed sample products:

```bash
docker exec -it project_api php artisan db:seed --class=ProductSeeder
```

## API Endpoints

All API routes are registered under Laravel's `/api` prefix.

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/plain-ok` | Plain text health check returning `OK`. |
| GET | `/api/json-ok` | JSON health check returning `{ "ok": true }`. |
| GET | `/api/products` | Returns the first 5 products with `id`, `name`, and `price`. |
| GET | `/api/redis` | Returns the first 5 products using Redis cache key `products`. |
| GET | `/api/user` | Returns the authenticated Sanctum user. |

Example:

```bash
curl http://localhost:8000/api/json-ok
```

## Shared Services

The shared services are available on the following local ports:

| Service | Container | Host Port | Internal Host |
| --- | --- | --- | --- |
| MySQL | `shared_mysql` | `3307` -> `3306` | `shared_mysql:3306` |
| Redis | `shared_redis` | `6379` | `shared_redis:6379` |
| Mailpit SMTP | `shared_mailpit` | `1025` | `shared_mailpit:1025` |
| Mailpit UI | `shared_mailpit` | `8025` | `http://localhost:8025` |
| MinIO API | `shared_minio` | `9000` | `shared_minio:9000` |
| MinIO Console | `shared_minio` | `9001` | `http://localhost:9001` |

## Useful Commands

```bash
# Start shared infrastructure
cd shared-services && docker compose up -d

# Stop shared infrastructure
cd shared-services && docker compose down

# Start the API
cd docker-laravel/project-api && docker compose up -d --build

# View API logs
cd docker-laravel/project-api && docker compose logs -f app

# Run tests
docker exec -it project_api php artisan test

# Clear Laravel cache
docker exec -it project_api php artisan optimize:clear
```

## Documentation

- `ARCHITECTURE.md` explains the runtime architecture, request flow, services, and data model.
- `docker-laravel/project-api/README.md` documents the Laravel API application in more detail.
