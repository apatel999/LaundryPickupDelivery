using System.ComponentModel.DataAnnotations;


using System.ComponentModel.DataAnnotations;

namespace LaundryLoop.Api.Models;

// Inbound from the HTML form
public class CartItemDto
{
    public string ItemType { get; set; } = string.Empty;
    public string ItemName { get; set; } = string.Empty;
    public string? Description { get; set; }
    public decimal Price { get; set; }
}
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

    [Required]
    public string LaundrySize { get; set; } = string.Empty;

    [Required]
    public List<CartItemDto> CartItems { get; set; } = new();

    [Required]
    public decimal TotalCost { get; set; }

    public string? Notes { get; set; }
}

// Outbound to the browser
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

    public string LaundrySize { get; set; } = string.Empty;
    public decimal TotalCost { get; set; }
    public string? Notes { get; set; }
}
