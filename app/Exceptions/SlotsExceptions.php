<?php

namespace App\Exceptions;

class SlotsExceptions extends \Exception
{
    public static function conflict(): static
    {
        return new static('Slots conflict', 409);
    }

    public static function holdNotFound(int $id): static
    {
        return new static("Hold '$id' not found", 404);
    }

    public static function notFound(int $id): static
    {
        return new static("Slot '$id' not found", 404);
    }

    public static function oversale(int $id): static
    {
        return new static("Slot '$id' is oversale error", 404);
    }

    public static function statusFlow(int $id, string $status): static
    {
        return new static("Hold '$id' error set status '$status'", 404);
    }
}
