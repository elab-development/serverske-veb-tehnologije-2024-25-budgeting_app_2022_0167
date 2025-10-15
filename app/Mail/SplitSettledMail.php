<?php

namespace App\Mail;

use App\Models\Split;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SplitSettledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Split $split;
    public bool $isSettled;

    public function __construct(Split $split, bool $isSettled)
    {
        $this->split = $split;
        $this->isSettled = $isSettled;
    }

    public function build()
    {
        $exp = $this->split->expense;
        $subject = $this->isSettled ? 'Učešće je označeno kao plaćeno' : 'Učešće je vraćeno na neplaćeno';

        return $this->subject($subject)
            ->markdown('emails.split-settled', [
                'split' => $this->split,
                'expense' => $exp,
                'payer' => $exp->payer,
                'isSettled' => $this->isSettled,
            ]);
    }
}
