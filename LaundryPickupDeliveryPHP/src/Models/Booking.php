<?php

declare(strict_types=1);

namespace LaundryLoop\Models;

class Booking
{
    public int     $id               = 0;
    public string  $reference        = '';
    public string  $apartmentName    = '';
    public string  $apartmentAddress = '';
    public string  $unitNumber       = '';
    public ?string $buzzerCode       = null;
    public string  $phone            = '';
    public ?string $email            = null;
    public string  $slotId           = '';
    public string  $slotDay          = '';
    public string  $slotPeriod       = '';
    public string  $pickupTime       = '';
    public string  $deliveryTime     = '';
    public string  $laundrySize      = '';
    public float   $totalCost        = 0.0;
    public ?string $notes            = null;
    public string  $status           = 'Pending';
    public string  $createdAt        = '';
    public string  $updatedAt        = '';

    /**
     * Hydrate from a PDO row (column names are PascalCase in DB).
     */
    public static function fromRow(array $row): self
    {
        $b                  = new self();
        $b->id               = (int)   ($row['Id']               ?? 0);
        $b->reference        = (string) ($row['Reference']        ?? '');
        $b->apartmentName    = (string) ($row['ApartmentName']    ?? '');
        $b->apartmentAddress = (string) ($row['ApartmentAddress'] ?? '');
        $b->unitNumber       = (string) ($row['UnitNumber']       ?? '');
        $b->buzzerCode       =           $row['BuzzerCode']       ?? null;
        $b->phone            = (string) ($row['Phone']            ?? '');
        $b->email            =           $row['Email']            ?? null;
        $b->slotId           = (string) ($row['SlotId']           ?? '');
        $b->slotDay          = (string) ($row['SlotDay']          ?? '');
        $b->slotPeriod       = (string) ($row['SlotPeriod']       ?? '');
        $b->pickupTime       = (string) ($row['PickupTime']       ?? '');
        $b->deliveryTime     = (string) ($row['DeliveryTime']     ?? '');
        $b->laundrySize      = (string) ($row['LaundrySize']      ?? '');
        $b->totalCost        = (float)  ($row['TotalCost']        ?? 0.0);
        $b->notes            =           $row['Notes']            ?? null;
        $b->status           = (string) ($row['Status']           ?? 'Pending');
        $b->createdAt        = (string) ($row['CreatedAt']        ?? '');
        $b->updatedAt        = (string) ($row['UpdatedAt']        ?? '');
        return $b;
    }

    /**
     * Serialize to response array — mirrors MapToResponse() in BookingsController.cs
     */
    public function toResponse(): array
    {
        return [
            'id'               => $this->id,
            'reference'        => $this->reference,
            'status'           => $this->status,
            'createdAt'        => $this->createdAt,
            'apartmentName'    => $this->apartmentName,
            'apartmentAddress' => $this->apartmentAddress,
            'unitNumber'       => $this->unitNumber,
            'buzzerCode'       => $this->buzzerCode,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'slotDay'          => $this->slotDay,
            'slotPeriod'       => $this->slotPeriod,
            'pickupTime'       => $this->pickupTime,
            'deliveryTime'     => $this->deliveryTime,
            'laundrySize'      => $this->laundrySize,
            'totalCost'        => $this->totalCost,
            'notes'            => $this->notes,
        ];
    }
}
