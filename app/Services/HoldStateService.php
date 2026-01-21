<?php

namespace App\Services;

use App\Contracts\HoldStateRepositoryInterface;
use App\Contracts\HoldStateServiceInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис изменения статуса холда.
 */
class HoldStateService implements HoldStateServiceInterface
{
    /**
     * @param HoldStateRepositoryInterface $repository изменяет таблицу holds.
     */
    public function __construct(
        protected HoldStateRepositoryInterface $repository
    ) {}

    /**
     * inheritDoc
     */
    public function confirm(int $holdId): bool
    {
        return $this->apply(fn (): bool => $this->repository->confirm($holdId));
    }

    /**
     * inheritDoc
     */
    public function cancel(int $holdId): bool
    {
        return $this->apply(fn (): bool => $this->repository->cancel($holdId));
    }

    /**
     * Общая логика после изменения статуса.
     *
     * @param callable $action
     * @return bool
     */
    private function apply(callable $action): bool
    {
        if (! $action()) {
            return false;
        }

        Cache::forget('slots:availability');

        return true;
    }
}
