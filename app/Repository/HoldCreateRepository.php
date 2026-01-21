<?php

namespace App\Repository;

use App\Contracts\HoldCreateRepositoryInterface;
use App\Contracts\SlotCapacityRepositoryInterface;
use App\Enums\HoldStatus;
use App\Exceptions\SlotsExceptions;
use App\Models\Hold;
use App\Services\SlotHolderService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Создание записи в таблице holds
 */
readonly class HoldCreateRepository implements HoldCreateRepositoryInterface
{
    /**
     * @param SlotCapacityRepositoryInterface $holdActive получения активных холдов
     */
    public function __construct(
        private SlotCapacityRepositoryInterface $holdActive
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

            $expiresAt = $now->copy()->addMinutes(SlotHolderService::HOLD_TTL_MINUTES);
            $holdId = DB::table('holds')->insertGetId([
                'slots_id' => $slotId,
                'status' => HoldStatus::Held->value,
                'expires_at' => $expiresAt,
            ]);

            return Hold::query()->findOrFail($holdId);
        });
    }

}
