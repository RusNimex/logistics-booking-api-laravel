<?php

namespace App\Repository;

use App\Contracts\HoldCreateRepositoryInterface;
use App\Contracts\SlotCapacityRepositoryInterface;
use App\Enums\HoldStatus;
use App\Exceptions\SlotsExceptions;
use App\Models\Hold;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Создание записи в таблице holds
 */
class HoldCreateRepository implements HoldCreateRepositoryInterface
{
    /**
     * Время жизни холда в минутах
     */
    private const int HOLD_TTL_MINUTES = 5;

    /**
     * @param SlotCapacityRepositoryInterface $holdActive - получения активных холдов
     */
    public function __construct(
        private readonly SlotCapacityRepositoryInterface $holdActive
    ) {}

    /**
     * @inheritDoc
     */
    public function create(int $slotId): Hold
    {
        return DB::transaction(function () use ($slotId) {
            $slot = DB::table('slots')
                ->select(['id', 'capacity', 'remaining'])
                ->where('id', $slotId)
                ->lockForUpdate()
                ->first();

            if ($slot === null) {
                throw SlotsExceptions::notFound($slotId);
            }

            $now = Carbon::now();
            $activeHolds = $this->holdActive->countHolds($slotId);

            if ($activeHolds + 1 > $slot->capacity) {
                throw SlotsExceptions::conflict();
            }

            // Просроченные слоты не обнуляются, уберем эту проблему
            if ($slot->remaining > $slot->capacity) {
                throw SlotsExceptions::oversale($slotId);
            }

            $expiresAt = $now->copy()->addMinutes(self::HOLD_TTL_MINUTES);
            $holdId = DB::table('holds')->insertGetId([
                'slots_id' => $slotId,
                'status' => HoldStatus::Held->value,
                'expires_at' => $expiresAt,
            ]);

            DB::table('slots')
                ->where('id', $slotId)
                ->update(['remaining' => $slot->remaining + 1]);

            return Hold::query()->findOrFail($holdId);
        });
    }

}
