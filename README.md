# Resumind

Job application and interview tracking system built with Laravel and Filament.

## About Resumind

Resumind is a personal career management tool that helps users organize and track their job applications, manage resumes, and prepare for interviews. Built with Laravel 12 and Filament 3 for a modern admin interface.

## Features

- **Resume Management**: Store and organize multiple resume versions
- **Job Application Tracking**: Track applications with status, dates, and company info
- **Interview Scheduling**: Manage interview schedules and preparation
- **Application Questions**: Store and organize common interview questions per application
- **User Management**: Multi-user support with Filament admin panel

## Tech Stack

- **Backend**: Laravel 12
- **Admin Panel**: Filament 3
- **Database**: SQLite (default)
- **Testing**: PHPUnit
- **Code Quality**: Laravel Pint

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js (for frontend assets)

### Installation

```bash
# Clone the repository
git clone <repo-url>
cd resumind

# Install PHP dependencies
composer install

# Set up environment
cp .env.example .env
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Install frontend dependencies
npm install
npm run build

# Start development server
composer run dev
```

### Development Commands

```bash
# Start all services (server, queue, logs, vite)
composer run dev

# Run tests
composer run test

# Code formatting
php artisan pint
```

## Project Structure

- `app/Models/` - Eloquent models (User, Resume, JobApplication, Interview, ApplicationQuestion)
- `app/Filament/` - Filament admin panel resources
- `app/Enums/` - Application enums
- `database/migrations/` - Database migrations
- `database/seeders/` - Database seeders

## Models

- **User**: System users
- **Resume**: User resumes with versions
- **JobApplication**: Job applications with status tracking
- **Interview**: Interview records linked to applications
- **ApplicationQuestion**: Common questions per application

## License

MIT
