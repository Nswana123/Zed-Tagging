<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\tickets;
use App\Models\User;
use App\Models\user_group;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyTicketSummaryEmail;

class DailyTicketSummary extends Command
{
    protected $signature = 'daily-ticket-summary';
    protected $description = 'Send daily ticket summaries to users';

    public function handle()
    {
        $today = now()->format('Y-m-d');
        $totalOutOfTimeTickets = 0;
        $allEmailsSent = [];
        $allCcEmailsSent = [];
        
        // Get tickets closed today and "out of time," grouped by escalation_group
        $ticketsByGroup = tickets::whereDate('closed_date', $today)
            ->where('ticket_age', 'Out Of Time')
            ->get()
            ->groupBy('escalation_group');
        
        foreach ($ticketsByGroup as $groupId => $tickets) {
            $ticketCount = $tickets->count();
            $totalOutOfTimeTickets += $ticketCount;
        
            // Check if escalation group is null; if so, default to 'Back Office' group
            if (is_null($groupId)) {
                $groupName = 'Back Office';
                $userEmails = User::whereHas('user_group', function($query) {
                    $query->where('group_name', 'Back Office');
                })->pluck('email')->toArray();
            } else {
                $groupName = user_group::where('id', $groupId)->value('group_name');
                $userEmails = User::where('group_id', $groupId)->pluck('email')->toArray();
            }
        
            Log::info("Escalation group {$groupName} has {$ticketCount} tickets.");
            Log::info("Emails in escalation group {$groupName}: " . json_encode($userEmails));
        
            // Get CC emails for BO Supervisor and CC Supervisor
            $ccEmails = User::whereHas('user_group', function($query) {
                $query->whereIn('group_name', ['BO Supervisor', 'CC Supervisor']);
            })->pluck('email')->toArray();
            Log::info("CC Emails for BO and CC Supervisors: " . json_encode($ccEmails));
        
            // Send a single email to all users in the group, with CCs
            try {
                Mail::to($userEmails)
                    ->cc($ccEmails)
                    ->send(new DailyTicketSummaryEmail($tickets, $today, $ticketCount, $groupName));
        
                Log::info("Email sent to group: " . implode(', ', $userEmails) . " with CC: " . implode(', ', $ccEmails));
                $allEmailsSent = array_merge($allEmailsSent, $userEmails);
                $allCcEmailsSent = array_merge($allCcEmailsSent, $ccEmails);
        
            } catch (\Exception $e) {
                Log::error("Failed to send email to group {$groupName}: " . $e->getMessage());
            }
        }
        
        // Final summary log after job completion
        $uniqueEmails = array_unique($allEmailsSent);
        $uniqueCcEmails = array_unique($allCcEmailsSent);
        

        $this->info("Total Out Of Time Tickets: {$totalOutOfTimeTickets}");
        $this->info("Emails sent to: " . implode(', ', $uniqueEmails));
        $this->info("CC Emails sent to: " . implode(', ', $uniqueCcEmails));

        Log::info("Total Out Of Time Tickets: {$totalOutOfTimeTickets}");
        Log::info("Emails sent to: " . implode(', ', $uniqueEmails));
        Log::info("CC Emails sent to: " . implode(', ', $uniqueCcEmails));

        $this->info('Daily ticket summary job dispatched!');
    }
}
