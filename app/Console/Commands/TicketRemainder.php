<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\tickets;
use App\Models\User;
use App\Models\user_group;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketRemainderEmail;

class TicketRemainder extends Command
{
    protected $signature = 'daily-ticket-reminder';
    protected $description = 'Send ticket remainder notifications to users';

    public function handle()
    {
        $today = now()->format('Y-m-d');
        $totalOutOfTimeTickets = 0;
        $allEmailsSent = [];
        
        // Get tickets with status 'inprogress' and 'refund' is NULL
        $tickets = tickets::where('ticket_status', 'inprogress')
                          ->whereNull('refund')
                          ->get();
        
        // Group tickets by escalation group
        $ticketsByGroup = $tickets->groupBy('escalation_group');
        
        foreach ($ticketsByGroup as $groupId => $tickets) {
            $ticketCount = $tickets->count();
            $totalOutOfTimeTickets += $ticketCount;
        
            // Check if escalation group is null; if so, default to 'Back Office' group
            if (is_null($groupId)) {
                $groupName = 'back office';
                $userEmails = User::whereHas('user_group', function($query) {
                    $query->where('group_name', 'back office');
                })->pluck('email')->toArray();
            } else {
                $groupName = user_group::where('id', $groupId)->value('group_name');
                $userEmails = User::where('group_id', $groupId)->pluck('email')->toArray();
            }
        
            Log::info("Escalation group {$groupName} has {$ticketCount} tickets.");
            Log::info("Emails in escalation group {$groupName}: " . json_encode($userEmails));
        
            // Merge all emails into a list of emails to be sent
            $allEmailsSent = array_merge($allEmailsSent, $userEmails);
        
            // Send the email
            try {
                Mail::to($userEmails)
                    ->send(new TicketRemainderEmail($tickets, $today, $ticketCount, $groupName, $allEmailsSent));

                Log::info("Email sent to group: " . implode(', ', $userEmails));
            } catch (\Exception $e) {
                Log::error("Failed to send email to group {$groupName}: " . $e->getMessage());
            }
        }
        
        // Final summary log after job completion
        $uniqueEmails = array_unique($allEmailsSent);
        
        $this->info("Total Out Of Time Tickets: {$totalOutOfTimeTickets}");
        $this->info("Emails sent to: " . implode(', ', $uniqueEmails));

        Log::info("Total Out Of Time Tickets: {$totalOutOfTimeTickets}");
        Log::info("Emails sent to: " . implode(', ', $uniqueEmails));

        $this->info('Ticket remainder job dispatched!');
    }
}
