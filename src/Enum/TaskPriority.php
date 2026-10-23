<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskPriority: string
{
    case Low = 'Low';
    case Medium = 'Medium';
    case High = 'High';
    case Critical = 'Critical';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
