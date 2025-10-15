<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
 
class SplitResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'expense_id' => $this->expense_id,
            'user'       => new UserResource($this->whenLoaded('user')),
            'user_id'    => $this->when(! $this->relationLoaded('user'), $this->user_id),
            'amount'     => (float) $this->amount,
            'settled_at' => optional($this->settled_at)?->toISOString(),

            // ako je učitan i sam expense, možemo ga dati
            'expense'    => new ExpenseResource($this->whenLoaded('expense')),
        ];
    }
}
