using System.ComponentModel.DataAnnotations;

namespace LaundryLoop.Api.Models;

// ── Inbound from the HTML form ──
public class CreateBookingRequest
{
    [Required]
    public string ApartmentName { get; set; } = string.Empty;

    [Required]
    public string ApartmentAddress { get; set; } = string.Empty;

    [Required]
    public string UnitNumber { get; set; } = string.Empty;

    public string? BuzzerCode { get; set; }

    [Required, Phone]
    public string Phone { get; set; } = string.Empty;

    [EmailAddress]
    public string? Email { get; set; }

    [Required]
    public string SlotId { get; set; } = string.Empty;

    [Required]
    public string SlotDay { get; set; } = string.Empty;

    [Required]
    public string SlotPeriod { get; set; } = string.Empty;

    [Required]
    public string PickupTime { get; set; } = string.Empty;

    [Required]
    public string DeliveryTime { get; set; } = string.Empty;

    [Required, MinLength(1)]
    public List<string> Services { get; set; } = new();

    public List<string>? Addons { get; set; }

    public string? Notes { get; set; }
}

// ── Outbound to the browser ──
public class BookingResponse
{
    public int Id { get; set; }
    public string Reference { get; set; } = string.Empty;
    public string Status { get; set; } = string.Empty;
    public DateTime CreatedAt { get; set; }

    public string ApartmentName { get; set; } = string.Empty;
    public string ApartmentAddress { get; set; } = string.Empty;
    public string UnitNumber { get; set; } = string.Empty;
    public string? BuzzerCode { get; set; }

    public string Phone { get; set; } = string.Empty;
    public string? Email { get; set; }

    public string SlotDay { get; set; } = string.Empty;
    public string SlotPeriod { get; set; } = string.Empty;
    public string PickupTime { get; set; } = string.Empty;
    public string DeliveryTime { get; set; } = string.Empty;

    public string Services { get; set; } = string.Empty;
    public string? Addons { get; set; }
    public string? Notes { get; set; }
}
