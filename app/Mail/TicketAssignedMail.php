<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
    public $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }
    
    public function build()
    {
        return $this->view('email.ticket_assigned')
                    ->with(['ticket' => $this->ticket])
                    ->subject('New Ticket Assignment');
    }
}
