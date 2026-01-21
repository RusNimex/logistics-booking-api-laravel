<?php

namespace App\Repository;

use App\Contracts\SlotCapacityRepositoryInterface;
use App\Enums\HoldStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Репозиторий для получения данных по слоту и его активных холдов.
 */
readonly class SlotCapacityRepository implements SlotCapacityRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function get(int $id): ?array
    {
        $slot = DB::table('slots')
            ->select(['id', 'capacity'])
            ->where('id', $id)
            ->first();

        if ($slot === null) {
            return null;
        }

        return [
            'id' => $slot->id,
            'capacity' => $slot->capacity,
            'active_holds' => $this->countHolds($id),
        ];
    }

    /**
     * Количество активных холдов (held с TTL + confirmed).
     *
     * @param int $slotId
     * @return int
     */
    public function countHolds(int $slotId): int
    {
        $now = Carbon::now();

        return DB::table('holds')
            ->where('slots_id', $slotId)
            ->where(function ($query) use ($now) {
                $query
                    ->where(function ($subQuery) use ($now) {
                        $subQuery->where('status', HoldStatus::Held->value)
                            ->where('expires_at', '>', $now);
                    })
                    ->orWhere('status', HoldStatus::Confirmed->value);
            })
            ->count();
    }
}
