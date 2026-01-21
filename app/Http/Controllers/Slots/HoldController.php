<?php

namespace App\Http\Controllers\Slots;

use App\Contracts\SlotHolderServiceInterface;
use App\Exceptions\SlotsExceptions;
use App\Http\Controllers\Controller;
use App\Http\Resources\HoldPayloadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Обслуживание бронирования слотов
 */
class HoldController extends Controller
{
    /**
     * @param SlotHolderServiceInterface $service - сервисный слой (кэш, транзакции, идемпотентность)
     */
    public function __construct(
        private readonly SlotHolderServiceInterface $service
    ) {}

    /**
     * Создание холда в слоте
     *
     * Метод: POST /slots/{id}/hold
     *
     * Создает запись в таблице holds со статусом held.
     * Проверяет доступность мест и возвращает 409 Conflict, если capacity исчерпан.
     * Повторный запрос с тем же ключом возвращает прежний результат (идемпотентность).
     * Холды живут 5 минут (фоновую очистку можно не реализовывать).
     * @throws SlotsExceptions
     */
    public function create(Request $request, int $id): JsonResponse
    {
        $idempotencyKey = (string) $request->attributes->get('idempotency_key');
        $payload = $this->service->create($id, $idempotencyKey);

        return HoldPayloadResource::make($payload)->response();
    }

    /**
     * Подтверждение холда в слоте
     *
     * Метод: POST /slots/{id}/confirm
     */
    public function confirm(int $id): JsonResponse
    {
        //
    }

    /**
     * Отмена холда в слоте
     *
     * Метод: POST /slots/{id}/canceled
     */
    public function canceled(int $id): JsonResponse
    {
        //
    }
}
