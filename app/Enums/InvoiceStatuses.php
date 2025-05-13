<?php

namespace App\Enums;

enum InvoiceStatuses: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    /**
     * Get the color associated with each status.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Paid => 'green',
            self::Overdue => 'red',
            self::Cancelled => 'gray',
        };
    }

    /**
     * Get all names of the statuses.
     */
    public static function getNames(): array
    {
        return array_map(fn($status) => $status->name, self::cases());
    }

    /**
     * Get all values of the statuses.
     */
    public static function getValues(): array
    {
        return array_map(fn($status) => $status->value, self::cases());
    }
}
