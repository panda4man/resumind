# Resumind Agents & Automation

This document outlines the agents and automation workflows available in the Resumind project.

## Available Agents

### Admin Panel Agent (Filament)

Filament 3 provides an admin panel for managing:
- Users
- Resumes
- Job Applications
- Interviews
- Application Questions

Access at `/admin` after authentication.

### Queue Worker

Background job processing for:
- Email notifications
- Data exports
- Scheduled reminders
- Interview preparation notifications

Run with:
```bash
php artisan queue:listen
```

### Scheduler

Automated tasks scheduled in `app/Console/Kernel.php`:
- Application status updates
- Interview reminders
- Resume backup notifications

## Development Agents

### Code Quality Agent

Automated code formatting and linting:
```bash
php artisan pint
```

Runs on all PHP files in `app/` and tests.

### Testing Agent

Automated test suite for validation:
```bash
composer run test
```

## Integration Points

### Email Notifications

System can send notifications for:
- Application status changes
- Upcoming interviews
- Resume updates
- New application questions

Configure in `.env` with mail settings.

### Data Export

Export functionality for:
- Application history
- Resume versions
- Interview notes

## Configuration

Key environment variables in `.env`:
- `APP_DEBUG` - Debug mode toggle
- `QUEUE_CONNECTION` - Queue driver (database, redis, sync)
- `MAIL_MAILER` - Email service provider
- `DATABASE_URL` - Database connection string

