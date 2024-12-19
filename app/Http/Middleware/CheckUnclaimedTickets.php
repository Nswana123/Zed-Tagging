<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUnclaimedTickets
{  public function handle(Request $request, Closure $next)
    {
        // Check for tickets that are open and unclaimed
        $this->checkUnclaimedTickets();

        // Continue with the request
        return $next($request);
    }

    /**
     * Function to check and update tickets that have been open for more than 1 minute.
     */
    private function checkUnclaimedTickets()
    {
        // Get all tickets that are open and unclaimed
        $tickets = tickets::where('claim_status', 'open')
                          ->where('ticket_status', 'open')
                          ->get();

        foreach ($tickets as $ticket) {
            $createdAt = Carbon::parse($ticket->created_at);
            $minutesSinceCreated = $createdAt->diffInMinutes(Carbon::now());

            // If ticket is open for more than 1 minute, mark as unclaimed
            if ($minutesSinceCreated > 1) {
                $ticket->claim_status = 'unclaimed';
                $ticket->save();
            }
        }
    }
}
