<?php

namespace App\Contracts;

/**
 * Репозиторий для изменения статуса холда
 */
interface HoldStateRepositoryInterface
{
    /**
     * Изменение статуса холда
     *
     * @param int $holdId ID холда
     * @return bool
     */
    public function confirm(int $holdId): bool;

    /**
     * Удаление холда
     *
     * @param int $holdId
     * @return bool
     */
    public function cancel(int $holdId): bool;

}
