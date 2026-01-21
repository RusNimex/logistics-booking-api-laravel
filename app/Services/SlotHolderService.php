<?php

namespace App\Services;

use App\Contracts\HoldCreateRepositoryInterface;
use App\Contracts\SlotHolderServiceInterface;
use App\Contracts\SlotCapacityRepositoryInterface;
use App\Exceptions\SlotsExceptions;
use App\Models\Hold;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

readonly class SlotHolderService implements SlotHolderServiceInterface
{
    /**
     * @param SlotCapacityRepositoryInterface $slot - данных о слоте
     * @param HoldCreateRepositoryInterface $hold - создания холда
     */
    public function __construct(
        private SlotCapacityRepositoryInterface $slot,
        private HoldCreateRepositoryInterface   $hold
    ) {}

    /**
     * @inheritDoc
     *
     * @throws SlotsExceptions
     */
    public function create(int $slotId, string $idempotencyKey): Hold
    {
        $cacheKey = "idempotency:hold:{$idempotencyKey}";
        $cached = Cache::get($cacheKey);

        if ($cached instanceof Hold) {
            return $cached;
        }

        if (is_array($cached) && isset($cached['hold_id'])) {
            $hold = Hold::query()->find($cached['hold_id']);

            if ($hold instanceof Hold) {
                return $hold;
            }
        }

        $slot = $this->slot->get($slotId);

        if ($slot === null) {
            throw SlotsExceptions::notFound($slotId);
        }

        if ($slot['active_holds'] + 1 > $slot['capacity']) {
            throw SlotsExceptions::conflict();
        }

        $hold = $this->hold->create($slotId);

        Cache::put($cacheKey, $hold, Carbon::now()->addMinutes(self::IDEMPOTENCY_TTL_MINUTES));

        return $hold;
    }

}
