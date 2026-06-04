# Resumind Development Guidelines

## Project Overview

Resumind is a Laravel 12 + Filament 3 job application and interview tracking system. This document provides guidelines for development.

## Architecture

### Layers

- **Models** (`app/Models/`): Eloquent ORM models for data entities
- **Filament Resources** (`app/Filament/`): Admin panel configuration
- **Actions** (`app/Actions/`): Reusable business logic
- **Enums** (`app/Enums/`): Type-safe enum definitions
- **HTTP** (`app/Http/`): Controllers and middleware

### Data Models

- **User**: System users with authentication
- **Resume**: User resumes with versioning
- **JobApplication**: Job applications with status tracking
- **Interview**: Interview records linked to applications
- **ApplicationQuestion**: Common interview questions per application

## Development Workflow

### Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Running Locally

```bash
# Start all services (server, queue, logs, vite)
composer run dev
```

### Testing

```bash
composer run test
```

### Code Quality

```bash
# Format code
php artisan pint
```

## Key Conventions

- Use Eloquent relationships for data access
- Leverage Filament resources for admin panel UI
- Keep business logic in Actions or Models
- Use Enums for type-safe status/state values
- Write tests for critical features

## Configuration

Key `.env` variables:

- `APP_DEBUG` - Debug mode (set to false in production)
- `QUEUE_CONNECTION` - Background job queue driver
- `MAIL_MAILER` - Email service
- `DATABASE_URL` - Database connection

## Deployment

- Docker support via `Dockerfile` and `docker/` directory
- AWS CodeBuild integration via `buildspec.yml`
- Queue worker runs background jobs
- Scheduler handles automated tasks
