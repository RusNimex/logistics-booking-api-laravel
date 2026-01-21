<?php

namespace App\Enums;

enum HoldStatus: string
{
    case Held = 'held';
    case Confirmed = 'confirmed';
    case Canceled = 'canceled';

    public static function values(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::cases()
        );
    }
}
