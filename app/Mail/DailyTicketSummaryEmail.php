<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;

class DailyTicketSummaryEmail extends Mailable
{
    public $tickets;
    public $date;
    public $ticketCount;
    public $groupName;

    public function __construct($tickets, $date, $ticketCount, $groupName)
    {
        $this->tickets = $tickets;
        $this->date = $date;
        $this->ticketCount = $ticketCount;
        $this->groupName = $groupName;
    }

    public function build()
    {
        return $this->view('email.daily_ticket_summary')
            ->subject('Daily Ticket Summary')
            ->with([
                'tickets' => $this->tickets,
                'date' => $this->date,
                'ticketCount' => $this->ticketCount,
                'groupName' => $this->groupName,
            ]);
    }
}
