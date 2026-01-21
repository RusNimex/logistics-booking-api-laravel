<?php

namespace App\Http\Controllers\Slots;

use App\Contracts\SlotServiceCollectorInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\SlotResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Контроллер получения списка слотов.
 */
class AvailabilityController extends Controller
{
    /**
     * @param SlotServiceCollectorInterface $service - сервис {@see SlotServiceCollector}
     */
    public function __construct(
        protected SlotServiceCollectorInterface $service
    ) {}

    /**
     * Список слотов.
     *
     * @return AnonymousResourceCollection
     */
    public function __invoke(): AnonymousResourceCollection
    {
        return SlotResource::collection($this->service->getCollection());
    }

}
