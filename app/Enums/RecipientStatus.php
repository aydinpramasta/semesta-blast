<?php

namespace App\Enums;

enum RecipientStatus: int
{
    case Requested = 1;
    case Queued = 2;
    case Sent = 3;

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Diajukan',
            self::Queued => 'Diantrikan',
            self::Sent => 'Terkirim',
        };
    }
}
