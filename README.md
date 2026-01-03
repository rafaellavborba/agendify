# Agendify

Agendify is a web-based appointment management system designed to help businesses and service providers manage appointments, clients, and services efficiently. Built with Laravel on the backend, it features a clean architecture with filters, CRUD operations, and reusable traits.

## Features

- **Appointments CRUD**: Create, read, update, and delete appointments.
- **Services CRUD**: Manage services with filters and sorting.
- **Tenants CRUD**: Multi-tenant support for businesses.
- **Filtering & Sorting**: Search and sort appointments and services easily.
- **Reusable Traits**: Shared logic for consistent behavior across models.

## Installation

1. Clone the repository:

```bash
git clone https://github.com/rafaellavborba/agendify.git
cd agendify
```

2. Install dependencies via Composer:

```bash 
composer install
```

3. Copy the environment file and set your configuration:

```bash
cp .env.example .env
php artisan key:generate
```

4. Set up your database in the .env file and run migrations:

```bash
php artisan migrate
```

5. Set up your database in the .env file and run migrations:

```bash
php artisan migrate
```

## USAGE

Use the API endpoints to manage appointments, services, and tenants.

Supports filtering and sorting of services and appointments.

Authentication is handled via Sanctum (API tokens).

## TECHNOLOGIES

**Backend**: Laravel 12, PHP 8+

**Database**: MySQL / MariaDB (configurable)

**Authentication**: Laravel Sanctum

**Testing**: PHPUnit

## Contributing

Fork the repository.

Create a new branch: git checkout -b feature/your-feature.

Make your changes.

Commit your changes: git commit -m 'Add new feature'.

Push to the branch: git push origin feature/your-feature.

Open a Pull Request.

## License

This project is open-source and available under the MIT License.