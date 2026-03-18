using Dapper;
using LaundryLoop.Api.Models;
using Microsoft.Data.SqlClient;

namespace LaundryLoop.Api.Data;

public class BookingRepository
{
    private readonly string _connectionString;

    public BookingRepository(IConfiguration config)
    {
        _connectionString = config.GetConnectionString("DefaultConnection")
            ?? throw new InvalidOperationException("Connection string 'DefaultConnection' is missing.");
    }

    private SqlConnection Connect() => new(_connectionString);

    // CREATE
    public async Task<Booking> InsertAsync(Booking booking)
    {
        const string sql = """
                INSERT INTO Bookings (
                    Reference, ApartmentName, ApartmentAddress, UnitNumber, BuzzerCode,
                    Phone, Email, SlotId, SlotDay, SlotPeriod, PickupTime, DeliveryTime,
                    LaundrySize, TotalCost, Notes, Status, CreatedAt, UpdatedAt
                )
                VALUES (
                    @Reference, @ApartmentName, @ApartmentAddress, @UnitNumber, @BuzzerCode,
                    @Phone, @Email, @SlotId, @SlotDay, @SlotPeriod, @PickupTime, @DeliveryTime,
                    @LaundrySize, @TotalCost, @Notes, @Status, @CreatedAt, @UpdatedAt
                );
                SELECT CAST(SCOPE_IDENTITY() AS INT);
                """;

        using var conn = Connect();
        var id = await conn.ExecuteScalarAsync<int>(sql, booking);
        booking.Id = id;
        return booking;
    }

    // CREATE CartItem
    public async Task<CartItem> InsertCartItemAsync(CartItem cartItem)
    {
        const string sql = """
                INSERT INTO CartItems (BookingId, ItemType, ItemName, Description, Price)
                VALUES (@BookingId, @ItemType, @ItemName, @Description, @Price);
                SELECT CAST(SCOPE_IDENTITY() AS INT);
                """;

        using var conn = Connect();
        var id = await conn.ExecuteScalarAsync<int>(sql, cartItem);
        cartItem.Id = id;
        return cartItem;
    }

    // READ CartItems for booking
    public async Task<IEnumerable<CartItem>> GetCartItemsAsync(int bookingId)
    {
        const string sql = "SELECT * FROM CartItems WHERE BookingId = @BookingId ORDER BY Id";
        using var conn = Connect();
        return await conn.QueryAsync<CartItem>(sql, new { BookingId = bookingId });
    }

    // READ — single by ID
    public async Task<Booking?> GetByIdAsync(int id)
    {
        const string sql = "SELECT * FROM Bookings WHERE Id = @Id";
        using var conn = Connect();
        return await conn.QuerySingleOrDefaultAsync<Booking>(sql, new { Id = id });
    }

    // READ — list with filter, sort + pagination
    public async Task<(int Total, IEnumerable<Booking> Items)> GetAllAsync(
        int page, int pageSize, string? status, string? sortBy, string? sortDir)
    {
        // Whitelist sort columns to prevent SQL injection
        var allowedSort = new Dictionary<string, string>(StringComparer.OrdinalIgnoreCase)
        {
            ["reference"] = "Reference",
            ["apartmentname"] = "ApartmentName",
            ["apartmentaddress"] = "ApartmentAddress",
            ["unitnumber"] = "UnitNumber",
            ["slotday"] = "SlotDay",
            ["pickuptime"] = "PickupTime",
            ["status"] = "Status",
            ["createdat"] = "CreatedAt",
        };

        var orderCol = allowedSort.TryGetValue(sortBy ?? "", out var col) ? col : "CreatedAt";
        var orderDir = string.Equals(sortDir, "asc", StringComparison.OrdinalIgnoreCase) ? "ASC" : "DESC";
        var where = string.IsNullOrWhiteSpace(status) ? "" : "WHERE Status = @Status";
        var offset = (page - 1) * pageSize;

        var sql = $"""
                SELECT COUNT(*) FROM Bookings {where};

                SELECT * FROM Bookings {where}
                ORDER BY {orderCol} {orderDir}
                OFFSET @Offset ROWS FETCH NEXT @PageSize ROWS ONLY;
                """;

        var param = new { Status = status, Offset = offset, PageSize = pageSize };

        using var conn = Connect();
        using var multi = await conn.QueryMultipleAsync(sql, param);

        var total = await multi.ReadSingleAsync<int>();
        var items = await multi.ReadAsync<Booking>();

        return (total, items);
    }

    // UPDATE — status only
    public async Task<Booking?> UpdateStatusAsync(int id, string status)
    {
        const string sql = """
                UPDATE Bookings
                SET Status = @Status, UpdatedAt = @UpdatedAt
                WHERE Id = @Id;
                SELECT * FROM Bookings WHERE Id = @Id;
                """;

        using var conn = Connect();
        return await conn.QuerySingleOrDefaultAsync<Booking>(sql, new
        {
            Id = id,
            Status = status,
            UpdatedAt = DateTime.UtcNow,
        });
    }

    // DELETE
    public async Task<bool> DeleteAsync(int id)
    {
        const string sql = "DELETE FROM Bookings WHERE Id = @Id";
        using var conn = Connect();
        var rows = await conn.ExecuteAsync(sql, new { Id = id });
        return rows > 0;
    }
}