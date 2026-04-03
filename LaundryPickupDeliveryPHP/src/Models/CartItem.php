<?php

declare(strict_types=1);

namespace LaundryLoop\Models;

class CartItem
{
    public int     $id          = 0;
    public int     $bookingId   = 0;
    public string  $itemType    = '';   // 'laundry', 'addon', 'delivery'
    public string  $itemName    = '';
    public ?string $description = null;
    public float   $price       = 0.0;

    public static function fromRow(array $row): self
    {
        $c              = new self();
        $c->id          = (int)   ($row['Id']          ?? 0);
        $c->bookingId   = (int)   ($row['BookingId']   ?? 0);
        $c->itemType    = (string) ($row['ItemType']    ?? '');
        $c->itemName    = (string) ($row['ItemName']    ?? '');
        $c->description =           $row['Description'] ?? null;
        $c->price       = (float)  ($row['Price']       ?? 0.0);
        return $c;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'bookingId'   => $this->bookingId,
            'itemType'    => $this->itemType,
            'itemName'    => $this->itemName,
            'description' => $this->description,
            'price'       => $this->price,
        ];
    }
}
