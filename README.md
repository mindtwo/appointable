# Appointable by mindtwo

## Introduction

**Appointable** is a Laravel package by mindtwo that provides an appointment management system with configurable routes and controllers. It allows developers to manage appointments, handle invitations, and provide easy-to-use RESTful endpoints for various appointment-related actions. This package comes with built-in routes, a service provider, and configuration options for quick integration.

## Features

- Create and auto-manage appointments for Eloquent models
- Registerable routes for managing appointments.
- RESTful controllers for creating, updating, canceling, and managing invitations.
- Middleware support for secure appointment routes.

## Installation

1. Install the package via Composer:

   ```bash
   composer require mindtwo/appointable
   ```

2. (Optional) Publish the configuration and migration files:

   ```bash
   php artisan vendor:publish --provider="mindtwo\Appointable\Providers\AppointableServiceProvider" --tag="appointable-config"
   php artisan vendor:publish --provider="mindtwo\Appointable\Providers\AppointableServiceProvider" --tag="appointable-migrations"
   ```

3. Run the migrations:

   ```bash
   php artisan migrate
   ```

## Usage

### Register Routes

To register the default appointment management routes, simply call the `Appointable::routes()` method in your `web.php` routes file.

```php
use mindtwo\Appointable\Facades\Appointable;

// Register the default appointment routes
Appointable::routes();
```

By default, the routes will be registered under the `/appointments` prefix. You can customize the prefix and middleware by passing options to the `routes()` method:

```php
Appointable::routes('my-appointments', ['auth']);
```

This will register the routes under `/my-appointments` and apply the `auth` middleware.

### Available Routes

The following routes are registered when using `Appointable::routes()`:

- **GET** `/appointments` – List all appointments (Index).
- **POST** `/appointments` – Create a new appointment.
- **PUT|PATCH** `/appointments/{uuidOrUid}` – Update an appointment.
- **DELETE** `/appointments/{uuidOrUid}` – Cancel an appointment.
- **POST** `/appointments/{uuidOrUid}/confirm` – Confirm an appointment invitation.
- **POST** `/appointments/{uuidOrUid}/decline` – Decline an appointment invitation.

### Middleware

By default, no middleware is applied to the routes. You can specify custom middleware in the `routes()` method:

```php
Appointable::routes('appointments', ['auth', 'verified']);
```

### Configurations

You can customize the behavior of the package through its configuration file. After publishing the configuration, modify the `config/appointable.php` file as needed.

```php
return [
    'middleware' => ['web'],
];
```

## Migrations

The package provides a migration file to create the `appointments` table. You can publish the migration with:

```bash
php artisan vendor:publish --provider="mindtwo\Appointable\Providers\AppointableServiceProvider" --tag="appointable-migrations"
```

After publishing, run the migration:

```bash
php artisan migrate
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
