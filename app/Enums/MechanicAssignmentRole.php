<?php

namespace App\Enums;

enum MechanicAssignmentRole: string
{
    case Lead = 'lead';
    case Assistant = 'assistant';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Mekanik Utama',
            self::Assistant => 'Asisten',
        };
    }
}
