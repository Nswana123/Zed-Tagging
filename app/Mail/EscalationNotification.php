<?php

namespace App\Mail;

use App\Models\tickets;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EscalationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $user;

    public function __construct(tickets $ticket, $user)
    {
        $this->ticket = $ticket;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('New Ticket Escalation')
            ->view('email.escalationNotification')
            ->with([
                'ticket' => $this->ticket,
                'user' => $this->user,
            ]);
    }
}
