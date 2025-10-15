<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
 
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'role'  => $this->role,

            
            'counts' => [
                'expenses_paid' => $this->whenCounted('expensesPaid'),
                'splits'        => $this->whenCounted('splits'),
                'settlements_sent' => $this->whenCounted('settlementsSent'),
                'settlements_received' => $this->whenCounted('settlementsReceived'),
            ],
        ];
    }
}
