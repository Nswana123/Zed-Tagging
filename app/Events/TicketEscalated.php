<?php
namespace App\Events;

use App\Models\tickets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketEscalated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $users;

    public function __construct(tickets $ticket, $users)
    {
        $this->ticket = $ticket;
        $this->users = $users;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('escalation-group.' . $this->ticket->escalation_group);
    }

    public function broadcastAs()
    {
        return 'ticket.escalated';
    }
}
