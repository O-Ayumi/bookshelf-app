<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Unread = 'unread';
    case Reading = 'in_progress';
    case Completed = 'completed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Unread => '未読',
            self::Reading => '読書中',
            self::Completed => '読了',
            self::Expired => '期限切れ',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Unread => 'bg-gray-100 text-gray-800',
            self::Reading => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Expired => 'bg-red-100 text-red-800',
        };
    }
}
