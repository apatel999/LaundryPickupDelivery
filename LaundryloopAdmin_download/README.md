# LaundryLoop — .NET Core API

## Project structure

```
FreshFold/
├── FreshFold.Api/
│   ├── Controllers/
│   │   └── BookingsController.cs   ← REST endpoints
│   ├── Data/
│   │   └── BookingRepository.cs    ← Dapper queries (plain SQL)
│   ├── Models/
│   │   ├── Booking.cs              ← DB row model
│   │   └── BookingDto.cs           ← Request / Response DTOs
│   ├── appsettings.json
│   ├── appsettings.Development.json
│   └── Program.cs
├── setup-database.sql              ← Run once to create the table
└── laundryloop-booking.html        ← Frontend
```

## Prerequisites

- [.NET 8 SDK](https://dotnet.microsoft.com/download)
- SQL Server (local or Azure SQL)

---

## Quick start

### 1. Create the database

Run `setup-database.sql` against your SQL Server **once**.

In SSMS or Azure Data Studio: open the file and hit Execute.

Or via command line:
```bash
sqlcmd -S localhost -i setup-database.sql
```

### 2. Update the connection string

Edit `appsettings.json`:
```json
"ConnectionStrings": {
  "DefaultConnection": "Server=localhost;Database=FreshFoldDb;Trusted_Connection=True;TrustServerCertificate=True;"
}
```

Azure SQL example:
```
Server=tcp:yourserver.database.windows.net,1433;Database=FreshFoldDb;User ID=youruser;Password=yourpassword;Encrypt=True;
```

### 3. Run the API
```bash
cd FreshFold.Api
dotnet run
```

### 4. Open Swagger
```
http://localhost:5000/swagger
```

### 5. Point the frontend at the API

In `laundryloop-booking.html` update:
```js
const API_BASE = 'http://localhost:5000';
```

---

## API endpoints

| Method   | Route                       | Description           |
|----------|-----------------------------|-----------------------|
| `POST`   | `/api/bookings`             | Create a new booking  |
| `GET`    | `/api/bookings`             | List all (paginated)  |
| `GET`    | `/api/bookings/{id}`        | Get one booking       |
| `PATCH`  | `/api/bookings/{id}/status` | Update status         |
| `DELETE` | `/api/bookings/{id}`        | Delete a booking      |

### POST /api/bookings — request body
```json
{
  "apartmentName":    "Maple Towers",
  "apartmentAddress": "120 Maple Ave",
  "unitNumber":       "4B",
  "buzzerCode":       "#456",
  "phone":            "(416) 555-0123",
  "email":            "alex@email.com",
  "slotId":           "sat-morn",
  "slotDay":          "Saturday",
  "slotPeriod":       "Morning",
  "pickupTime":       "8 - 10 am",
  "deliveryTime":     "Same day, 4 - 6 pm",
  "services":         ["Wash", "Ironing"],
  "addons":           ["Fabric softener"],
  "notes":            "Leave with concierge"
}
```

### PATCH /api/bookings/{id}/status
```json
{ "status": "PickedUp" }
```
Valid statuses: `Pending` · `Confirmed` · `PickedUp` · `Delivered` · `Cancelled`

---

## Why Dapper instead of Entity Framework?

No ORM magic — every query is plain readable SQL inside `BookingRepository.cs`.
Easy to debug, easy to optimise, zero migration tooling needed.

|                | Dapper              | Entity Framework         |
|----------------|---------------------|--------------------------|
| Query style    | Plain SQL           | LINQ / generated SQL     |
| Setup          | Package + SQL script| Migrations + DbContext   |
| Performance    | Minimal overhead    | More abstraction layers  |
| Flexibility    | Full SQL control    | ORM conventions          |

---

## Production checklist

- [ ] Lock CORS in `Program.cs` — replace `AllowAnyOrigin()` with your real domain
- [ ] Add auth to `GET /api/bookings` (admin listing endpoint)
- [ ] Move connection string to environment variables or Azure Key Vault
- [ ] Add rate limiting on `POST /api/bookings`
- [ ] Send SMS via Twilio when a booking is created
