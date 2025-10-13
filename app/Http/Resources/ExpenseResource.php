<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
 
class ExpenseResource extends JsonResource
{
    public function toArray($request): array
    { 
        $desc = $this->description ?? $this->note;

        return [
            'id'          => $this->id,
            'paid_by'     => new UserResource($this->whenLoaded('payer')),
            'paid_by_id'  => $this->when(! $this->relationLoaded('payer'), $this->paid_by),
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'category_id' => $this->when(! $this->relationLoaded('category'), $this->category_id),

            'paid_at'     => optional($this->paid_at)?->toISOString(),
            'amount'      => (float) $this->amount,
            'description' => $desc,
 
            'splits'      => SplitResource::collection($this->whenLoaded('splits')),
        ];
    }
}
