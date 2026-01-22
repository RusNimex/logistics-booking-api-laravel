<?php

namespace App\Services;

use App\Contracts\HoldCreateRepositoryInterface;
use App\Contracts\SlotHolderServiceInterface;
use App\Contracts\SlotCapacityRepositoryInterface;
use App\Exceptions\SlotsExceptions;
use App\Models\Hold;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис для создания холда в слоте с поддержкой идемпотентности в запросах.
 */
readonly class SlotHolderService implements SlotHolderServiceInterface
{
    /**
     * Время жизни холда в минутах
     */
    const int HOLD_TTL_MINUTES = 5;

    /**
     * Время жизни кеша в минутах
     */
    const int CACHE_TTL_MINUTES = 5;

    /**
     * @param SlotCapacityRepositoryInterface $slot данные о слоте
     * @param HoldCreateRepositoryInterface $hold создание холда
     */
    public function __construct(
        private SlotCapacityRepositoryInterface $slot,
        private HoldCreateRepositoryInterface   $hold,
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

        $slot = $this->slot->get($slotId);

        if ($slot === null) {
            throw SlotsExceptions::notFound($slotId);
        }

        if ($slot['active_holds'] + 1 > $slot['capacity']) {
            throw SlotsExceptions::conflict();
        }

        $hold = $this->hold->create($slotId);

        Cache::put($cacheKey, $hold, Carbon::now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $hold;
    }

}
