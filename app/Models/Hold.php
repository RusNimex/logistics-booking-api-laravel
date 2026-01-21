<?php

namespace App\Models;

use App\Enums\HoldStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Бронирование слота
 *
 * @property int $id
 * @property int $slots_id
 * @property HoldStatus $status
 * @property Carbon|null $expires_at
 */
class Hold extends Model
{
    protected $table = 'holds';
    public $timestamps = false;

    protected $fillable = [
        'slots_id',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'status' => HoldStatus::class,
        'expires_at' => 'datetime',
    ];
}
