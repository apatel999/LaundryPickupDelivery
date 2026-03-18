using System.ComponentModel.DataAnnotations;

namespace LaundryLoop.Api.Models;

public class CartItem
{
    public int Id { get; set; }

    [Required]
    public int BookingId { get; set; }

    [Required, MaxLength(50)]
    public string ItemType { get; set; } = string.Empty; // 'laundry', 'addon', 'delivery'

    [Required, MaxLength(100)]
    public string ItemName { get; set; } = string.Empty;

    [MaxLength(200)]
    public string? Description { get; set; }

    [Required]
    public decimal Price { get; set; }

    // Navigation
    public Booking? Booking { get; set; }
}