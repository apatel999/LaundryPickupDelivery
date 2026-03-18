
using LaundryLoop.Api.Data;
using LaundryLoop.Api.Models;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace LaundryLoop.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class BookingsController : ControllerBase
{
    private readonly BookingRepository _repo;
    private readonly ILogger<BookingsController> _logger;

    public BookingsController(BookingRepository repo, ILogger<BookingsController> logger)
    {
        _repo   = repo;
        _logger = logger;
    }

    // POST /api/bookings
    [HttpPost]
    public async Task<IActionResult> Create([FromBody] CreateBookingRequest req)
    {
        if (!ModelState.IsValid)
            return BadRequest(ModelState);

        var booking = new Booking
        {
            Reference        = GenerateReference(),
            ApartmentName    = req.ApartmentName.Trim(),
            ApartmentAddress = req.ApartmentAddress.Trim(),
            UnitNumber       = req.UnitNumber.Trim(),
            BuzzerCode       = req.BuzzerCode?.Trim(),
            Phone            = req.Phone.Trim(),
            Email            = req.Email?.Trim().ToLowerInvariant(),
            SlotId           = req.SlotId,
            SlotDay          = req.SlotDay,
            SlotPeriod       = req.SlotPeriod,
            PickupTime       = req.PickupTime,
            DeliveryTime     = req.DeliveryTime,
            LaundrySize      = req.LaundrySize,
            TotalCost        = req.TotalCost,
            Notes            = req.Notes?.Trim(),
            Status           = "Pending",
            CreatedAt        = DateTime.UtcNow,
            UpdatedAt        = DateTime.UtcNow,
        };

        await _repo.InsertAsync(booking);

        // Insert cart items
        foreach (var item in req.CartItems)
        {
            var cartItem = new CartItem
            {
                BookingId = booking.Id,
                ItemType = item.ItemType,
                ItemName = item.ItemName,
                Description = item.Description,
                Price = item.Price
            };
            await _repo.InsertCartItemAsync(cartItem);
        }

        _logger.LogInformation("Booking created: {Ref} — {Apt} unit {Unit}",
            booking.Reference, booking.ApartmentName, booking.UnitNumber);

        return CreatedAtAction(nameof(GetById), new { id = booking.Id }, MapToResponse(booking));
    }

    // GET /api/bookings/{id}
    [HttpGet("{id:int}")]
    public async Task<IActionResult> GetById(int id)
    {
        var booking = await _repo.GetByIdAsync(id);
        return booking is null ? NotFound() : Ok(MapToResponse(booking));
    }

    // GET /api/bookings?page=1&pageSize=50&status=Pending&sortBy=apartmentAddress&sortDir=asc
    [Authorize]
    [HttpGet]
    public async Task<IActionResult> GetAll(
        [FromQuery] int page       = 1,
        [FromQuery] int pageSize   = 50,
        [FromQuery] string? status  = null,
        [FromQuery] string? sortBy  = "createdAt",
        [FromQuery] string? sortDir = "desc")
    {
        var (total, items) = await _repo.GetAllAsync(page, pageSize, status, sortBy, sortDir);

        return Ok(new
        {
            total,
            page,
            pageSize,
            items = items.Select(MapToResponse),
        });
    }

    // PATCH /api/bookings/{id}/status
    // Body: { "status": "PickedUp" }
    [Authorize]
    [HttpPatch("{id:int}/status")]
    public async Task<IActionResult> UpdateStatus(int id, [FromBody] UpdateStatusRequest req)
    {
        var allowed = new[] { "Pending", "Confirmed", "PickedUp", "Delivered", "Cancelled" };
        if (!allowed.Contains(req.Status))
            return BadRequest(new { error = $"Invalid status. Allowed: {string.Join(", ", allowed)}" });

        var booking = await _repo.UpdateStatusAsync(id, req.Status);
        return booking is null ? NotFound() : Ok(MapToResponse(booking));
    }

    // DELETE /api/bookings/{id}
    [Authorize]
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Delete(int id)
    {
        var deleted = await _repo.DeleteAsync(id);
        return deleted ? NoContent() : NotFound();
    }

    // Helpers
    private static string GenerateReference()
        => $"LLP-{Random.Shared.Next(100_000, 999_999)}";

    private static BookingResponse MapToResponse(Booking b) => new()
    {
        Id               = b.Id,
        Reference        = b.Reference,
        Status           = b.Status,
        CreatedAt        = b.CreatedAt,
        ApartmentName    = b.ApartmentName,
        ApartmentAddress = b.ApartmentAddress,
        UnitNumber       = b.UnitNumber,
        BuzzerCode       = b.BuzzerCode,
        Phone            = b.Phone,
        Email            = b.Email,
        SlotDay          = b.SlotDay,
        SlotPeriod       = b.SlotPeriod,
        PickupTime       = b.PickupTime,
        DeliveryTime     = b.DeliveryTime,
        LaundrySize     = b.LaundrySize,
        TotalCost        = b.TotalCost,
        Notes            = b.Notes,
    };


    // GET /api/bookings/{id}/cart-items
    [HttpGet("{id:int}/cart-items")]
    public async Task<IActionResult> GetCartItems(int id)
    {
        var items = await _repo.GetCartItemsAsync(id);
        return Ok(items);
    }

    // ─────────────────────────────────────────
    // GET /api/config
    // ─────────────────────────────────────────
    [HttpGet("/api/config")]
    public IActionResult GetConfig([FromServices] IConfiguration config)
    {
        var baseUrl = config["BaseUrl"] ?? "http://localhost:5000";
        return Ok(new { baseUrl });
    }
}

public record UpdateStatusRequest(string Status);
