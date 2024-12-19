<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\tickets;
use Carbon\Carbon;

class UpdateTicketClaimStatus extends Command
{
    protected $signature = 'update-claim-status';
    protected $description = 'Update claim status of tickets to unclaimed if no one claims them for over an hour';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get all tickets that are 'open' and still claimed
        $tickets = tickets::where('ticket_status', 'open')
            ->where('claim_status', 'open')
            ->get();

        $this->info("Number of open, claimed tickets: " . $tickets->count());
        $now = Carbon::now();

    $this->info("Current Time: {$now}");

    foreach ($tickets as $ticket) {
        $this->info("Ticket ID {$ticket->id} created at: {$ticket->created_at}");
        $diffInMinutes = $ticket->created_at->diffInHours($now);
        $this->info("Time difference in minutes for Ticket ID {$ticket->id}: {$diffInMinutes}");

        if ($diffInMinutes >= 2) {
            $this->info("Updating claim status for Ticket ID {$ticket->id}");
            $ticket->claim_status = 'unclaimed';
            $ticket->save();
            $this->info('Ticket ID ' . $ticket->id . ' claim status changed to unclaimed.');
        } else {
            $this->info("Ticket ID {$ticket->id} not updated; created less than 1 minute ago.");
        }
    }
        $this->info('Ticket claim status check completed.');
    }
}
