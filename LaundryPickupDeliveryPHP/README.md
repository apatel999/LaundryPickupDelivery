# LaundryLoop API — PHP (Slim 4 + PDO + MySQL)

PHP port of the .NET Core `LaundryLoop.Api` project.  
Same routes, same request/response shapes, same Basic Auth protection.

## Stack

| .NET Core            | PHP Equivalent         |
|----------------------|------------------------|
| ASP.NET Core         | Slim 4                 |
| Dapper               | PDO (raw SQL)          |
| SQL Server           | MySQL                  |
| ILogger              | Monolog                |
| `[Authorize]`        | BasicAuthMiddleware    |
| `appsettings.json`   | `.env` file            |
| `Program.cs` DI      | PHP-DI container       |

## Setup

### 1. Install dependencies
```bash
composer install
```

### 2. Configure environment
```bash
cp .env.example .env
# Edit .env with your DB credentials and admin password
```

### 3. Create the database
```bash
mysql -u root -p < database/setup-database.sql
```

### 4. Run the server

**PHP built-in server (development):**
```bash
php -S localhost:8080 -t public
```

**Apache:** Point document root to `public/` — `.htaccess` handles routing.

**Nginx:**
```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

## API Endpoints

All endpoints mirror the .NET version exactly.

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| `POST`   | `/api/bookings`                   | Public | Create a booking |
| `GET`    | `/api/bookings/{id}`              | Public | Get booking by ID |
| `GET`    | `/api/bookings/{id}/cart-items`   | Public | Get cart items for booking |
| `GET`    | `/api/bookings`                   | 🔒 Basic | List all (paginated, filtered) |
| `PATCH`  | `/api/bookings/{id}/status`       | 🔒 Basic | Update booking status |
| `DELETE` | `/api/bookings/{id}`              | 🔒 Basic | Delete booking |
| `GET`    | `/api/config`                     | Public | Get base URL config |

### Query parameters for `GET /api/bookings`
```
?page=1&pageSize=50&status=Pending&sortBy=createdAt&sortDir=desc
```

### Status values
`Pending` · `Confirmed` · `PickedUp` · `Delivered` · `Cancelled`

### Basic Auth (admin routes)
```
Authorization: Basic base64(username:password)
```
Set `ADMIN_USERNAME` and `ADMIN_PASSWORD` in `.env`.

## Project Structure

```
LaundryPickupDelivery-PHP/
├── public/
│   ├── index.php          ← Entry point (mirrors Program.cs)
│   └── .htaccess
├── src/
│   ├── Controllers/
│   │   └── BookingsController.php   ← Mirrors BookingsController.cs
│   ├── Models/
│   │   ├── Booking.php              ← Mirrors Booking.cs
│   │   └── CartItem.php             ← Mirrors CartItem.cs
│   ├── Repository/
│   │   └── BookingRepository.php    ← Mirrors BookingRepository.cs (PDO instead of Dapper)
│   └── Middleware/
│       ├── BasicAuthMiddleware.php  ← Mirrors BasicAuthenticationHandler.cs
│       └── JsonMiddleware.php
├── config/
│   ├── Settings.php       ← Mirrors appsettings.json
│   └── Database.php       ← PDO connection factory
├── database/
│   └── setup-database.sql ← MySQL version of setup-database.sql
├── composer.json
├── .env.example
└── README.md
```
