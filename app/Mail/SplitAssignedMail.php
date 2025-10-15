<?php

namespace App\Mail;

use App\Models\Split;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SplitAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Split $split;

    public function __construct(Split $split)
    {
        $this->split = $split;
    }

    public function build()
    {
        $exp = $this->split->expense;
        return $this->subject('Novi trošak ti je dodeljen')
            ->markdown('emails.split-assigned', [
                'split' => $this->split,
                'expense' => $exp,
                'payer' => $exp->payer,
                'category' => $exp->category,
            ]);
    }
}
