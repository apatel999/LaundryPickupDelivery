using System.ComponentModel.DataAnnotations;

namespace LaundryLoopd.Api.Models;

public class Booking
{
    public int Id { get; set; }

    // Generated reference shown to user e.g. FFS-123456
    [Required, MaxLength(20)]
    public string Reference { get; set; } = string.Empty;

    // ── Location ──
    [Required, MaxLength(100)]
    public string ApartmentName { get; set; } = string.Empty;

    [Required, MaxLength(150)]
    public string ApartmentAddress { get; set; } = string.Empty;

    [Required, MaxLength(20)]
    public string UnitNumber { get; set; } = string.Empty;

    [MaxLength(50)]
    public string? BuzzerCode { get; set; }

    // ── Contact ──
    [Required, MaxLength(20)]
    public string Phone { get; set; } = string.Empty;

    [MaxLength(200)]
    public string? Email { get; set; }

    // ── Schedule ──
    // e.g. "fri-eve", "sat-morn", "sat-noon", "sun-morn", "sun-noon"
    [Required, MaxLength(20)]
    public string SlotId { get; set; } = string.Empty;

    [Required, MaxLength(50)]
    public string SlotDay { get; set; } = string.Empty;       // e.g. "Saturday"

    [Required, MaxLength(50)]
    public string SlotPeriod { get; set; } = string.Empty;    // e.g. "Morning"

    [Required, MaxLength(50)]
    public string PickupTime { get; set; } = string.Empty;    // e.g. "8 - 10 am"

    [Required, MaxLength(100)]
    public string DeliveryTime { get; set; } = string.Empty;  // e.g. "Same day, 4 - 6 pm"

    // ── Services ──
    // Stored as comma-separated values e.g. "Wash,Ironing"
    [Required, MaxLength(200)]
    public string Services { get; set; } = string.Empty;

    // ── Add-ons ──
    // e.g. "Fabric softener,Cold wash"
    [MaxLength(300)]
    public string? Addons { get; set; }

    [MaxLength(1000)]
    public string? Notes { get; set; }

    // ── Status ──
    [Required, MaxLength(30)]
    public string Status { get; set; } = "Pending"; // Pending | Confirmed | PickedUp | Delivered | Cancelled

    public DateTime CreatedAt { get; set; } = DateTime.UtcNow;
    public DateTime UpdatedAt { get; set; } = DateTime.UtcNow;
}
