<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
 
class SettlementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'from'    => new UserResource($this->whenLoaded('from')),
            'from_user_id' => $this->when(! $this->relationLoaded('from'), $this->from_user_id),

            'to'      => new UserResource($this->whenLoaded('to')),
            'to_user_id'   => $this->when(! $this->relationLoaded('to'), $this->to_user_id),

            'amount'  => (float) $this->amount,
            'paid_at' => optional($this->paid_at)?->toISOString(),
            'note'    => $this->note,
        ];
    }
}
