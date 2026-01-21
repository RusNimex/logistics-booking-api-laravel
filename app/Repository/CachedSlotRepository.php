<?php

namespace App\Repository;

use App\Contracts\SlotRepositoryInterface;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Random\RandomException;

/**
 * Декоратор для кеширования слотов, обертка для @see DbSlotRepository
 */
readonly class CachedSlotRepository implements SlotRepositoryInterface
{
    /**
     * Длительность сколько остальные ждут, пока кэш обновится.
     */
    private const float BLOCK_TTL = 0.5;

    /**
     * TTL блокировки, сколько lock считается живым.
     */
    private const int LOCK_SECOND = 5;

    /**
     * Минимальный и максимальный TTL кеша.
     */
    private const int MIN_TTL = 5;

    /**
     * Максимальный TTL кеша.
     */
    private const int MAX_TTL = 15;

    /**
     * @param SlotRepositoryInterface $inner - чтение слотов из базы
     */
    public function __construct(
        private SlotRepositoryInterface $inner
    ) {}

    /**
     * Слоты из кэша и stampede‑защитой.
     *
     * $ttl рандомный, чтоб избежать риска массового истечения кеша в один момент.
     * Если кэша нет — берём lock и обновляем кэш из базы.
     * @throws RandomException|LockTimeoutException
     */
    public function getCollection(): Collection
    {
        $key = 'slots:availability';
        $ttl = random_int(self::MIN_TTL, self::MAX_TTL);

        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $lock = Cache::lock('lock:slots:availability', self::LOCK_SECOND);

        try {
            if ($lock->block(self::BLOCK_TTL)) {
                $cached = Cache::get($key);
                if ($cached !== null) {
                    return $cached;
                }

                $fresh = $this->inner->getCollection();
                Cache::put($key, $fresh, $ttl);

                return $fresh;
            }
        } finally {
            $lock->release();
        }

        return Cache::get($key, collect());
    }

}
