<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\user_group;
use App\Models\tickets;
use App\Mail\HourlyReportMail;
use Illuminate\Support\Facades\Mail;

class SendHourlyReport extends Command
{
    protected $signature = 'send-hourly-report';
    protected $description = 'Send an hourly report to super admin users';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get counts of tickets by interaction status
        $resolvedCount = tickets::where('interaction_status', 'Resolved')->count();
        $escalatedCount = tickets::where('interaction_status', 'Escalated')->count();

        // Find the UserGroup ID for "super admin"
        $superAdminGroup = user_group::where('group_name', 'super admin')->first();

        if (!$superAdminGroup) {
            $this->info("No 'super admin' group found.");
            return;
        }

        
        $testEmail = 'cobet.nswana@zedmobile.co.zm';
        // Send email to each user in the "super admin" group

            Mail::to($testEmail)->send(new HourlyReportMail($resolvedCount, $escalatedCount));
         

            $this->info("Hourly report email sent to {$testEmail} for testing purposes.");
    }
}
