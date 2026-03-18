using System.ComponentModel.DataAnnotations;

namespace LaundryLoop.Api.Models;

public class Booking
{
    public int Id { get; set; }

    // Generated reference shown to user e.g. FFS-123456
    [Required, MaxLength(20)]
    public string Reference { get; set; } = string.Empty;

    // Location
    [Required, MaxLength(100)]
    public string ApartmentName { get; set; } = string.Empty;

    [Required, MaxLength(150)]
    public string ApartmentAddress { get; set; } = string.Empty;

    [Required, MaxLength(20)]
    public string UnitNumber { get; set; } = string.Empty;

    [MaxLength(50)]
    public string? BuzzerCode { get; set; }

    // Contact
    [Required, MaxLength(20)]
    public string Phone { get; set; } = string.Empty;

    [MaxLength(200)]
    public string? Email { get; set; }

    // Schedule
    [Required, MaxLength(20)]
    public string SlotId { get; set; } = string.Empty;

    [Required, MaxLength(50)]
    public string SlotDay { get; set; } = string.Empty;

    [Required, MaxLength(50)]
    public string SlotPeriod { get; set; } = string.Empty;

    [Required, MaxLength(50)]
    public string PickupTime { get; set; } = string.Empty;

    [Required, MaxLength(100)]
    public string DeliveryTime { get; set; } = string.Empty;

    // Laundry Size
    [Required, MaxLength(200)]
    public string LaundrySize { get; set; } = string.Empty;

    [Required]
    public decimal TotalCost { get; set; }

    [MaxLength(1000)]
    public string? Notes { get; set; }

    // Status
    [Required, MaxLength(30)]
    public string Status { get; set; } = "Pending";

    public DateTime CreatedAt { get; set; } = DateTime.UtcNow;
    public DateTime UpdatedAt { get; set; } = DateTime.UtcNow;
}
