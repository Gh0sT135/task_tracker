<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskStatus: string
{
    case Todo = 'To Do';
    case InProgress = 'In Progress';
    case Review = 'Review';
    case Done = 'Done';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
