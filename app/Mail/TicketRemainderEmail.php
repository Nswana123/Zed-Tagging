<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketRemainderEmail extends Mailable
{
    public $tickets;
    public $date;
    public $ticketCount;
    public $groupName;
    public $allEmailsSent;

    public function __construct($tickets, $date, $ticketCount, $groupName, $allEmailsSent)
    {
        $this->tickets = $tickets;
        $this->date = $date;
        $this->ticketCount = $ticketCount;
        $this->groupName = $groupName;
        $this->allEmailsSent = $allEmailsSent;
    }

    public function build()
    {
        return $this->view('email.ticket_reminder')
                    ->with([
                        'tickets' => $this->tickets,
                        'date' => $this->date,
                        'ticketCount' => $this->ticketCount,
                        'groupName' => $this->groupName,
                        'allEmailsSent' => implode(', ', $this->allEmailsSent),
                    ]);
    }
}
