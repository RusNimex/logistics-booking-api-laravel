<?php

namespace App\Http\Resources;

use App\Models\Hold;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HoldPayloadResource extends JsonResource
{
    /**
     * @param Hold $resource
     */
    public function __construct(Hold $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Hold $hold */
        $hold = $this->resource;

        return [
            'hold_id' => $hold->id,
            'slot_id' => $hold->slots_id,
            'status' => $hold->status->value,
            'expires_at' => $hold->expires_at?->toISOString(),
        ];
    }
}
