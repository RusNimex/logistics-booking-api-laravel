<?php

namespace App\Repository;

use App\Contracts\HoldStateRepositoryInterface;
use App\Enums\HoldStatus;
use App\Exceptions\SlotsExceptions;
use App\Models\Hold;
use Illuminate\Support\Facades\DB;

/**
 * Репозиторий для изменения статуса холда в таблице holds.
 */
class HoldStateRepository implements HoldStateRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function confirm(int $holdId): bool
    {
        return DB::transaction(function () use ($holdId) {
            $hold = $this->getHoldForUpdate($holdId);

            if ($hold->status === HoldStatus::Confirmed) {
                return true;
            }

            if ($hold->status !== HoldStatus::Held) {
                throw SlotsExceptions::statusFlow($hold->id, HoldStatus::Held->value);
            }

            $slot = $this->getSlotForUpdate($hold->slots_id);

            if ($slot->remaining <= 0) {
                throw SlotsExceptions::oversale($slot->id);
            }

            $this->updateSlotRemaining($slot->id, $slot->remaining - 1);

            $hold->status = HoldStatus::Confirmed;

            return $hold->save();
        });
    }

    /**
     * @inheritDoc
     */
    public function cancel(int $holdId): bool
    {
        return DB::transaction(function () use ($holdId) {
            $hold = $this->getHoldForUpdate($holdId);

            if ($hold->status === HoldStatus::Canceled) {
                return true;
            }

            $slot = $this->getSlotForUpdate($hold->slots_id);
            $this->updateSlotRemaining($slot->id, $slot->remaining + 1);

            $hold->status = HoldStatus::Canceled;

            return $hold->save();
        });
    }

    /**
     * Получение холда для обновления + lockForUpdate
     *
     * @param int $holdId
     * @return Hold
     * @throws SlotsExceptions
     */
    private function getHoldForUpdate(int $holdId): Hold
    {
        /** @var Hold|null $hold */
        $hold = Hold::query()
            ->whereKey($holdId)
            ->lockForUpdate()
            ->first();

        if ($hold === null) {
            throw SlotsExceptions::holdNotFound($holdId);
        }

        return $hold;
    }

    /**
     * Получение слота для обновления + lockForUpdate
     *
     * @param int $slotId
     * @return object
     * @throws SlotsExceptions
     */
    private function getSlotForUpdate(int $slotId): object
    {
        $slot = DB::table('slots')
            ->select(['id', 'capacity', 'remaining'])
            ->where('id', $slotId)
            ->lockForUpdate()
            ->first();

        if ($slot === null) {
            throw SlotsExceptions::notFound($slotId);
        }

        return $slot;
    }

    /**
     * Обновление количества свободных мест в слоте
     *
     * @param int $slotId
     * @param int $remaining
     */
    private function updateSlotRemaining(int $slotId, int $remaining): void
    {
        DB::table('slots')
            ->where('id', $slotId)
            ->update(['remaining' => $remaining]);
    }
}
