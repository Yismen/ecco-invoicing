<?php

namespace App\Enums;

enum InvoiceStatuses: string
{
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    /**
     * Get the color associated with each status.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::PartiallyPaid => 'info',
            self::Paid => 'success',
            self::Overdue => 'danger',
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
