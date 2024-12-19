<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Mail\TicketAssignedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\ticket_sla;
use App\Models\noc_attachment;
use App\Models\ticket_resolutions;
use App\Models\noc_ticket_tbl;
use App\Models\noc_assigned_tickets;
use App\Models\User;
use App\Models\fault_type_tbl;
use App\Models\user_group;
use App\Models\noc_resolutions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class NocTicketsController extends Controller
{
    public function viewNocTickets(Request $request)
    {
        
            try{
    
            $user = auth()->user();
            $permissions = $user->user_group->permissions;
            $userId = auth()->id();
            $inboxTickets = ticket_resolutions::selectRaw('
            MAX(id) as id, 
            ticket_id, 
            MAX(created_at) as created_at, 
            MAX(opened) as opened'
        )
        ->whereHas('tickets', function($query) use ($userId) {
            // Ensure the ticket is associated with the authenticated user
            $query->where('user_id', $userId)
                  ->where('closed', 'no')
                  ->where('opened', 'no'); // Only include records where closed is 'no'
        })
        ->groupBy('ticket_id')  // Group by ticket_id
        ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
        ->with(['tickets' => function ($query) {
            $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
        }])
        ->count(); 
        $tickets = [];
        if ($request->has('site_name') && strlen($request->input('site_name'))) {
            $siteName = $request->input('site_name'); // Get the site_name from the request
            $tickets = noc_ticket_tbl::where('site_name', $siteName) // Allow partial match
                ->orderBy('created_at', 'desc')
                ->get();

            if ($tickets->isEmpty()) {
                return redirect()->back()->withErrors('No tickets found for the specified site name.');
            }
        }
        $site_sla = fault_type_tbl::all();
            return view('noc_tickets.ticket', compact('permissions','inboxTickets','site_sla','tickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Noc ticketing Page ' . $e->getMessage());
        }
    
    }
    public function siteProfile($site_name)
    {
        // Fetch all tickets for the selected sit$site_name
        $tickets = noc_ticket_tbl::where('site_name', $site_name)
        ->orderBy('created_at', 'desc')
        ->get();
        $profileCount = noc_ticket_tbl::where('site_name', $site_name)  
        ->count();
        $profile = noc_ticket_tbl::get();

        $site_name= $profile->pluck('site_name')->unique()->count() === 1 ? $profile->pluck('site_name')->first() : 'Varies';
        foreach ($tickets as $ticket) {
            foreach ($tickets as $ticket) {
                if ($ticket->faulty_type) {
                    $currentTime = Carbon::now();
                    $createdTime = Carbon::parse($ticket->created_at);
                    $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
                    $serviceLevelInMinutes = $ttrInHours * 60; 
                    $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
                    $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
                    if ($timeRemainingInMinutes < 0) {
                        $timeRemainingInMinutes = 0;
                    }
                    $hoursRemaining = floor($timeRemainingInMinutes / 60);
                    $minutesRemaining = $timeRemainingInMinutes % 60;
                    $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
                    $ticket->time_remaining = [
                        'hours' => $hoursRemaining,
                        'minutes' => $minutesRemaining,
                        'totalMinutes' => $timeRemainingInMinutes,
                        'percentage' => $percentageRemaining,
                        'totalHours' => $ttrInHours
                    ];
                } else {
                    $ticket->time_remaining = [
                        'hours' => 0,
                        'minutes' => 0,
                        'totalMinutes' => 0,
                        'percentage' => 0,
                        'totalHours' => 0
                    ];
                }
            }
        }
        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count(); 
   
        return view('noc_tickets.SiteProfile', compact('tickets','permissions','inboxTickets','site_name','profileCount'));
    }
    public function LogTicket(Request $request)
{
    try{
        $validator = Validator::make($request->all(),[
        'site_name' => 'required|string|max:255',
        'sla_id' => 'required|exists:fault_type_tbl,id',
        'fault_severity' => 'required|string',
        'fault_occurrence_time' => 'required|date',
        'fault_description' => 'required|string',
        'attachments.*' => 'nullable|image|max:2048' // Optional images, 2MB max size each
    ]);
    if ($validator->fails()) {
        \Log::error($validator->errors());
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }
    $case_id = $this->generateCaseId();
    $ticket = noc_ticket_tbl::create([
        'case_id' => $case_id,
        'user_id' => Auth::id(),
        'site_name' => $request->site_name,
        'sla_id' => $request->sla_id,
        'fault_severity' => $request->fault_severity,
        'fault_occurrence_time' => $request->fault_occurrence_time,
        'fault_description' => $request->fault_description,
    ]);

    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $attachment) {
            $path = $attachment->store('attachments', 'public');
            noc_attachment::create([
                'tickets_id' => $ticket->id,
                'file_path' => $path,
            ]);
        }
    }
    return redirect()->back()->with('success', 'Ticket created successfully! Ticket #: ' . $ticket->case_id)->with('ticket', $ticket);
}catch (\Exception $e) {
    return redirect()->back()->withErrors('Failed to load Noc ticketing Page' . $e->getMessage());
}
}
private function generateCaseId()
{
    try{$prefix = 'Zed'; 

        do {
            $numeric_id = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $dateTimeSuffix = date('YmdHi');
            $case_id = $prefix . $numeric_id . $dateTimeSuffix;
        
        } while (noc_ticket_tbl::where('case_id', $case_id)->exists());

    return $case_id;
}
catch (\Exception $e) {
    return redirect()->back()->withErrors('Failed to generate Ticket id. ' . $e->getMessage());
}
}
public function viewOpenNocTicket(Request $request)
{
    
        try{

        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count(); 
    $tickets = noc_ticket_tbl::where('ticket_status', 'open')
      ->orderBy('created_at', 'desc')->get();
    foreach ($tickets as $ticket) {
        if ($ticket->faulty_type) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
            $serviceLevelInMinutes = $ttrInHours * 60; 
            $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
            $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
            if ($timeRemainingInMinutes < 0) {
                $timeRemainingInMinutes = 0;
            }
            $hoursRemaining = floor($timeRemainingInMinutes / 60);
            $minutesRemaining = $timeRemainingInMinutes % 60;
            $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
            $ticket->time_remaining = [
                'hours' => $hoursRemaining,
                'minutes' => $minutesRemaining,
                'totalMinutes' => $timeRemainingInMinutes,
                'percentage' => $percentageRemaining,
                'totalHours' => $ttrInHours
            ];
        } else {
            $ticket->time_remaining = [
                'hours' => 0,
                'minutes' => 0,
                'totalMinutes' => 0,
                'percentage' => 0,
                'totalHours' => 0
            ];
        }
    }
   
   
    $allOpenTickets = noc_ticket_tbl::where('ticket_status', 'open')->count();
    $user_group = User::with('user_group')
                ->whereHas('user_group', function($query) {
                    $query->where('group_name', 'Noc field Engineer'); // Adjust casing if necessary
                })
                ->where('status', 'active')
                ->get(); 
        return view('noc_tickets.NocOpenTickets', compact('permissions','inboxTickets','tickets','allOpenTickets', 'user_group'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Noc ticketing Page' . $e->getMessage());
    }

}
public function showNocTickets(Request $request,  $id)
{
    
        try{
            $ticket = noc_ticket_tbl::find($id);
            $resolutions = noc_resolutions::where('ticket_id', $ticket->id)->with('user')->get(); 
        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count(); 
    if ($ticket->faulty_type) {
        $currentTime = Carbon::now();
        $createdTime = Carbon::parse($ticket->created_at);
        $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
        $serviceLevelInMinutes = $ttrInHours * 60; 
        $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
        $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
        if ($timeRemainingInMinutes < 0) {
            $timeRemainingInMinutes = 0;
        }
        $hoursRemaining = floor($timeRemainingInMinutes / 60);
        $minutesRemaining = $timeRemainingInMinutes % 60;
        $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
        $ticket->time_remaining = [
            'hours' => $hoursRemaining,
            'minutes' => $minutesRemaining,
            'totalMinutes' => $timeRemainingInMinutes,
            'percentage' => $percentageRemaining,
            'totalHours' => $ttrInHours
        ];
    } else {
        $ticket->time_remaining = [
            'hours' => 0,
            'minutes' => 0,
            'totalMinutes' => 0,
            'percentage' => 0,
            'totalHours' => 0
        ];
    }


        return view('noc_tickets.showNocTickets', compact('permissions','inboxTickets','ticket','resolutions'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Open Noc tickete' . $e->getMessage());
    }

}
public function updateNocOpentickets(Request $request, $id)
{
    try{
        $validator = Validator::make($request->all(), [
            'resolution_remarks' => 'required|string|max:500',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = noc_ticket_tbl::findOrFail($id);

        $action = $request->input('action');
     
        if ($action == 'closed') {
            $ticket->ticket_status = 'closed';
            $closed_date = Carbon::now();
            $ticket->closed_by = Auth::user()->id;
            $ticket->closed_date = $closed_date;
            $root_cause = $request->input('root_cause');
            $ticket->root_cause = $root_cause;
            $time_taken = round($ticket->created_at->diffInHours($ticket->closed_date, true), 2);
     // Calculate outage duration in days
     $fault_occurrence_time = $ticket->fault_occurrence_time; // Assuming this is a valid date
     $outage_duration = $fault_occurrence_time 
         ? round($ticket->closed_date->diffInDays($fault_occurrence_time, true), 2) 
         : 0;
     $ticket->outage_duration = $outage_duration;

         $nocResolution = noc_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'opened' => 'closed',
                'closed' => 'closed',
            ]);
            $ticket->time_taken = $time_taken;
            $ttr_in_hour = $ticket->faulty_type->ttr_in_hour;

            if ($time_taken <= $ttr_in_hour) {
                $ticket->sla_compliance = 'Within Time';
            } else {
                $ticket->sla_compliance = 'Out of Time';
            }
          
        }
        

        // Handle file uploads if any attachments are present
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $ticket->attachments()->create([
                    'file_path' => $path,
                    'tickets_id' => $ticket->id,
                ]);
            }
        }
        $ticket->save();
    
       
        if ($action == 'closed') {
            return redirect('/noc_tickets.NocOpenTickets')->with('success', 'Ticket Closed successfully.');
        }}
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Close Noc Tickets. ' . $e->getMessage());
        } 
}
public function AssignNocTicket(Request $request)
{
    try {
        $ticket = noc_ticket_tbl::findOrFail($request->ticket_id);

        $request->validate([
            'engineer_id' => 'required|exists:users,id',
        ]);

        $ticket->ticket_status = 'inprogress';
        $ticket->save();

        $intUserTicket = new noc_assigned_tickets([
            'assigner_id' => Auth::id(),
            'ticket_id' => $ticket->id,
            'engineer_id' => $request->engineer_id,
        ]);
        $intUserTicket->save();
        
        $engineer = User::where('id', $request->engineer_id)->first();

        if ($engineer) {
            // Send email to the engineer
            Mail::to($engineer->email)->send(new TicketAssignedMail($ticket));

            // Prepare SMS message content for the engineer
            $smsMessage = "Ticket ID: " . $ticket->case_id . "\n" .
           "Opened At: " . $ticket->created_at . "\n" .
           "Site Name: " . $ticket->site_name . "\n" .
           "Fault Severity: " . $ticket->fault_severity . "\n" .
           "Time to Resolve: " . $ticket->faulty_type->ttr_in_hour . " hrs\n" .
           "Priority: " . ($ticket->faulty_type ? $ticket->faulty_type->priority : 'N/A') . "\n" .
           "Fault Occurrence: " . $ticket->fault_occurrence_time . "\n" .
           "Fault Type: " . $ticket->faulty_type->fault_type . "\n" .
           "Fault Description: " . $ticket->fault_description;

            // Use SMS API to send the message to the engineer's mobile
            $smsApiUrl =env('SMS_API_URL', 'https://172.28.14.2:9800/api/SubmitSMS/');
            $apiKey = env('SMS_API_KEY', 'eX3Q2vmmfgQNvCpJzbLeeNn9jBUn/ysfoHCxsEoR/g0=');
            $uid = env('SMS_API_UID', '100');
            $recipient = $engineer->mobile;  // Assuming the engineer has a 'mobile' field
            $smsMessage = strip_tags($smsMessage);  // Remove HTML tags for SMS

            $smsData = [
                'UId' => '100',
                'ApiKey' => $apiKey,
                'Recipient' => $recipient,
                'Message' => $smsMessage,
            ];

            // Send the SMS using HTTP POST
            $response = Http::withoutVerifying()->post($smsApiUrl, $smsData);

            // Check if the SMS was successfully sent
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Ticket assigned successfully! Email and SMS sent to ' . $engineer->email . ' and mobile number ' . $engineer->mobile . '!');
            } else {
                return redirect()->back()->with('error', 'Ticket assigned successfully, but SMS failed to send.');
            }
        }

        return redirect()->back()->with('success', 'Ticket assigned successfully! (Email not sent: Engineer not found)');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to assign NOC ticket. ' . $e->getMessage());
    }
}

public function viewAssignedNocTicket(Request $request)
{
    
        try{

        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count(); 
    $tickets = noc_ticket_tbl::with(['noc_assigned_tickets.engineer', 'noc_assigned_tickets.assigner', 'noc_resolutions'])
    ->where('ticket_status', 'inprogress')
    ->whereNull('escalation_status') 
    ->orderBy('created_at', 'desc') 
    ->get();
    $currentUser = Auth::user();
    $assignedTickets = noc_ticket_tbl::whereHas('noc_assigned_tickets', function ($query) use ($currentUser) {
        $query->where('engineer_id', $currentUser->id);
    })
    ->where('ticket_status', 'inprogress')  
    ->whereNull('escalation_status')    
    ->orderBy('created_at', 'desc')        
    ->with(['noc_assigned_tickets.engineer', 'noc_assigned_tickets.assigner', 'noc_resolutions'])            
    ->get(); 
    $MyassignedTickets = noc_ticket_tbl::whereHas('noc_assigned_tickets', function ($query) use ($currentUser) {
        $query->where('engineer_id', $currentUser->id);
    })
    ->where('ticket_status', 'inprogress')   
    ->whereNull('escalation_status')         
    ->with('noc_assigned_tickets.engineer')            
    ->count(); 
    foreach ($tickets as $ticket) {
        if ($ticket->faulty_type) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
            $serviceLevelInMinutes = $ttrInHours * 60; 
            $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
            $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
            if ($timeRemainingInMinutes < 0) {
                $timeRemainingInMinutes = 0;
            }
            $hoursRemaining = floor($timeRemainingInMinutes / 60);
            $minutesRemaining = $timeRemainingInMinutes % 60;
            $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
            $ticket->time_remaining = [
                'hours' => $hoursRemaining,
                'minutes' => $minutesRemaining,
                'totalMinutes' => $timeRemainingInMinutes,
                'percentage' => $percentageRemaining,
                'totalHours' => $ttrInHours
            ];
        } else {
            $ticket->time_remaining = [
                'hours' => 0,
                'minutes' => 0,
                'totalMinutes' => 0,
                'percentage' => 0,
                'totalHours' => 0
            ];
        }
    }
   
 foreach ($assignedTickets as $ticket) {
        if ($ticket->faulty_type) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
            $serviceLevelInMinutes = $ttrInHours * 60; 
            $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
            $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
            if ($timeRemainingInMinutes < 0) {
                $timeRemainingInMinutes = 0;
            }
            $hoursRemaining = floor($timeRemainingInMinutes / 60);
            $minutesRemaining = $timeRemainingInMinutes % 60;
            $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
            $ticket->time_remaining = [
                'hours' => $hoursRemaining,
                'minutes' => $minutesRemaining,
                'totalMinutes' => $timeRemainingInMinutes,
                'percentage' => $percentageRemaining,
                'totalHours' => $ttrInHours
            ];
        } else {
            $ticket->time_remaining = [
                'hours' => 0,
                'minutes' => 0,
                'totalMinutes' => 0,
                'percentage' => 0,
                'totalHours' => 0
            ];
        }
    }

    $allAssignedTickets = noc_ticket_tbl::where('ticket_status', 'inprogress')
    ->whereNull('escalation_status') ->count();
  
        return view('noc_tickets.NocAssignedTickets', compact('permissions','inboxTickets','tickets','allAssignedTickets','assignedTickets','MyassignedTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Noc Assigned Tickets' . $e->getMessage());
    }

}
public function showAssignedTickets(Request $request,  $id)
{
    
        try{
        $ticket = noc_ticket_tbl::find($id);
        $resolutions = noc_resolutions::where('ticket_id', $ticket->id)->with('user')
        ->orderBy('created_at', 'desc')->get(); 
        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count(); 
    if ($ticket->faulty_type) {
        $currentTime = Carbon::now();
        $createdTime = Carbon::parse($ticket->created_at);
        $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
        $serviceLevelInMinutes = $ttrInHours * 60; 
        $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
        $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
        if ($timeRemainingInMinutes < 0) {
            $timeRemainingInMinutes = 0;
        }
        $hoursRemaining = floor($timeRemainingInMinutes / 60);
        $minutesRemaining = $timeRemainingInMinutes % 60;
        $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
        $ticket->time_remaining = [
            'hours' => $hoursRemaining,
            'minutes' => $minutesRemaining,
            'totalMinutes' => $timeRemainingInMinutes,
            'percentage' => $percentageRemaining,
            'totalHours' => $ttrInHours
        ];
    } else {
        $ticket->time_remaining = [
            'hours' => 0,
            'minutes' => 0,
            'totalMinutes' => 0,
            'percentage' => 0,
            'totalHours' => 0
        ];
    }
    $user_group = user_group::all();

        return view('noc_tickets.showAssignedTickets', compact('permissions','inboxTickets','ticket','resolutions','user_group'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Assigned Noc tickete' . $e->getMessage());
    }

}
public function NocescalationGroup(Request $request)
{
  try{  $request->validate([
        'ticket_id' => 'required|exists:noc_ticket_tbl,id',
        'escalation_group' => 'required|exists:user_group,id',
    ]);

    // Find the corresponding entry in the user_tickets table
    $tickets = noc_ticket_tbl::find($request->ticket_id);

    if ($tickets) {
        // Update the user_id field with the new user
        $tickets->escalation_status = 'open';
        $tickets->escalation_group = $request->escalation_group;
        $tickets->escalation_date = Carbon::now();
        $tickets->save();
        $nocResolution = noc_resolutions::create([
            'ticket_id' => $request->ticket_id, 
            'user_id' => Auth::id(), 
            'resolution_remarks' => $request->resolution_remarks,
            'opened' => 'escalated',
            'closed' => 'escalated',
        ]);

    return redirect('/noc_tickets.NocAssignedTickets')->with('success', 'Ticket Escalated successfully!');
}
}
catch (\Exception $e) {
    return redirect()->back()->withErrors('Failed to Escalate a ticket. ' . $e->getMessage());
}
}
public function viewSiteFaults()
{
    try {
        $fault_type = fault_type_tbl::get();
        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count();  
        return view('noc_tickets.sitefaults', compact('permissions','fault_type','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Faulties. ' . $e->getMessage());
    }
}
public function addFaulty(Request $request)
    {
        try{
        $validatedData = $request->validate([
            'priority' => 'required|string|max:255',
            'fault_type' => 'required|string|max:255',
            'ttr_in_hour' => 'required|numeric|min:0', // Ensure TTR is a numeric value
        ]);
        fault_type_tbl::create([
            'priority' => $validatedData['priority'],
            'fault_type' => $validatedData['fault_type'],
            'ttr_in_hour' => $validatedData['ttr_in_hour'],
        ]);
        return redirect()->back()->with('success', 'Fault type created successfully!');
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Faulty. ' . $e->getMessage());
    }
}
    public function DeleteTFaults($id)
    {
        $ticket_sla = fault_type_tbl::findOrFail($id);
        $ticket_sla ->delete();
        return redirect()->route('noc_tickets.sitefaults')->with('success', 'Fault Type deleted successfully.');
    }
    public function editSiteFaulty($id)
    {
        try {
            $ticket_sla = fault_type_tbl::findOrFail($id);
            $user = auth()->user();
            $permissions = $user->user_group->permissions;
            $userId = auth()->id();
            $inboxTickets = ticket_resolutions::selectRaw('
            MAX(id) as id, 
            ticket_id, 
            MAX(created_at) as created_at, 
            MAX(opened) as opened'
        )
        ->whereHas('tickets', function($query) use ($userId) {
            // Ensure the ticket is associated with the authenticated user
            $query->where('user_id', $userId)
                  ->where('closed', 'no')
                  ->where('opened', 'no'); // Only include records where closed is 'no'
        })
        ->groupBy('ticket_id')  // Group by ticket_id
        ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
        ->with(['tickets' => function ($query) {
            $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
        }])
        ->count(); 
            return view('noc_tickets.editSiteFaulty', compact('ticket_sla','permissions','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to edit ticket sla. ' . $e->getMessage());
        }
    }
    public function updateSiteFaulty(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'priority' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'ttr_in_hour' => 'required|numeric|min:0',
            ]);
    
            $ticket_sla = fault_type_tbl::findOrFail($id);
            $ticket_sla->update([
                'priority' => $validatedData['priority'],
                'description' => $validatedData['description'],
                'ttr_in_hour' => $validatedData['ttr_in_hour'],
            ]);
   
            return redirect()->route('noc_tickets.SiteFaults')->with('success', 'ticket SLA updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to update Site faulty. ' . $e->getMessage());
        }
    }
    public function updateNocAssignedtickets(Request $request, $id)
{
    try{
        $validator = Validator::make($request->all(), [
            'resolution_remarks' => 'required|string|max:500',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = noc_ticket_tbl::findOrFail($id);

        $action = $request->input('action');
        if ($action == 'update') {
           $nocResolution = noc_resolutions::create([
            'ticket_id' => $id, 
            'user_id' => Auth::id(), 
            'resolution_remarks' => $request->resolution_remarks,
            'opened' => 'update',
            'closed' => 'update',
        ]);
    }
        elseif ($action == 'resolve') {
            $nocResolution = noc_resolutions::create([
             'ticket_id' => $id, 
             'user_id' => Auth::id(), 
             'resolution_remarks' => $request->resolution_remarks,
             'opened' => 'updated',
             'closed' => 'updated',
         ]);
        } 
        elseif ($action == 'closed') {
            $ticket->ticket_status = 'closed';
            $closed_date = Carbon::now();
            $ticket->closed_by = Auth::user()->id;
            $ticket->closed_date = $closed_date;
            $time_taken = round($ticket->created_at->diffInHours($ticket->closed_date, true), 2);
            $root_cause = $request->input('root_cause');
            $ticket->root_cause = $root_cause;
     // Calculate outage duration in days
        $fault_occurrence_time = $ticket->fault_occurrence_time; // Assuming this field exists in your model
        $fault_occurrence_time = $ticket->fault_occurrence_time; // Assuming this is a valid date
        $outage_duration = $fault_occurrence_time 
            ? round($ticket->closed_date->diffInDays($fault_occurrence_time, true), 2) 
            : 0;
        $ticket->outage_duration = $outage_duration;

         $nocResolution = noc_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'opened' => 'closed',
                'closed' => 'closed',
            ]);
            $ticket->time_taken = $time_taken;
            $ttr_in_hour = $ticket->faulty_type->ttr_in_hour;

            if ($time_taken <= $ttr_in_hour) {
                $ticket->sla_compliance = 'Within Time';
            } else {
                $ticket->sla_compliance = 'Out of Time';
            }
          
        }
        

        // Handle file uploads if any attachments are present
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $ticket->attachments()->create([
                    'file_path' => $path,
                    'tickets_id' => $ticket->id,
                ]);
            }
        }
        $ticket->save();
    
        if ($action == 'resolve') {
            return redirect('/noc_tickets.NocAssignedTickets')->with('success', 'Update Saved And Sent');
        }
        elseif ($action == 'update') {
            return redirect('/noc_tickets.NocAssignedTickets')->with('success', 'Update Saved And Sent');
        }
        elseif ($action == 'closed') {
            return redirect('/noc_tickets.NocAssignedTickets')->with('success', 'Ticket Closed successfully.');
        }}
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Close Noc Tickets. ' . $e->getMessage());
        } 
}
public function viewAllNocTicket(Request $request)
{
    ini_set('memory_limit', '10240M');
    try{

        $currentDate = Carbon::today(); // Set current date for daily filter
$startOfWeek = $currentDate->copy()->startOfWeek(); // Get the start of the current week
$endOfWeek = $currentDate->copy()->endOfWeek(); // Get the end of the current week

// Initialize the query builder with relations
$query = noc_ticket_tbl::with(['noc_assigned_tickets.engineer', 'noc_assigned_tickets.assigner'])
    ->whereIn('ticket_status', ['open', 'closed', 'inprogress']) // Filter by status
    ->orderBy('created_at', 'desc');

// Check if date filters are applied
if ($request->has('start_date') && $request->has('end_date')) {
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    // Apply the date filter with start_date and end_date from the request
    $query->whereDate('created_at', '>=', $request->start_date)
          ->whereDate('created_at', '<=', $request->end_date);
} else {
    // If no date filters are applied, default to showing weekly records
    $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
}

// Filter by MSISDN if provided
if ($case_id = $request->input('case_id')) {
    $query->where('case_id', $case_id);
}

// Filter by Case ID if provided
if ($site_name = $request->input('site_name')) {
    $query->where('site_name', $site_name);
}

// Count the tickets based on the filtered query
$allTickets = $query->count();

// Get the filtered tickets
$tickets = $query->get(); // Get the results

// (Optional) Add time remaining calculations if required
foreach ($tickets as $ticket) {
    if ($ticket->faulty_type) {
        $currentTime = Carbon::now();
        $createdTime = Carbon::parse($ticket->created_at);
        $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
        $serviceLevelInMinutes = $ttrInHours * 60;
        $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
        $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes;
        if ($timeRemainingInMinutes < 0) {
            $timeRemainingInMinutes = 0;
        }
        $hoursRemaining = floor($timeRemainingInMinutes / 60);
        $minutesRemaining = $timeRemainingInMinutes % 60;
        $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
        $ticket->time_remaining = [
            'hours' => $hoursRemaining,
            'minutes' => $minutesRemaining,
            'totalMinutes' => $timeRemainingInMinutes,
            'percentage' => $percentageRemaining,
            'totalHours' => $ttrInHours
        ];
    } else {
        $ticket->time_remaining = [
            'hours' => 0,
            'minutes' => 0,
            'totalMinutes' => 0,
            'percentage' => 0,
            'totalHours' => 0
        ];
    }
}


// Permissions and Inbox Tickets Calculation (No Change)
$user = auth()->user();
$permissions = $user->user_group->permissions;
$userId = auth()->id();
$inboxTickets = ticket_resolutions::selectRaw('
    MAX(id) as id, 
    ticket_id, 
    MAX(created_at) as created_at, 
    MAX(opened) as opened'
)
->whereHas('tickets', function($query) use ($userId) {
    $query->where('user_id', $userId)
          ->where('closed', 'no')
          ->where('opened', 'no'); // Only include records where closed is 'no'
})
->groupBy('ticket_id')  // Group by ticket_id
->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
->with(['tickets' => function ($query) {
    $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
}])
->count();

    return view('noc_tickets.NocAllTickets', compact('permissions','inboxTickets','tickets','allTickets'));

    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Noc All Tickets: ' . $e->getMessage());
    }
}

public function viewClosedNocTicket(Request $request)
{
    ini_set('memory_limit', '10240M');
    try{

        $currentDate = Carbon::today(); // Set current date for daily filter
$startOfWeek = $currentDate->copy()->startOfWeek(); // Get the start of the current week
$endOfWeek = $currentDate->copy()->endOfWeek(); // Get the end of the current week

// Initialize the query builder with relations
$query = noc_ticket_tbl::with(['noc_assigned_tickets.engineer', 'noc_assigned_tickets.assigner'])
    ->where('ticket_status', 'closed') // Filter by status
    ->orderBy('created_at', 'desc');

// Check if date filters are applied
if ($request->has('start_date') && $request->has('end_date')) {
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    // Apply the date filter with start_date and end_date from the request
    $query->whereDate('created_at', '>=', $request->start_date)
          ->whereDate('created_at', '<=', $request->end_date);
} else {
    // If no date filters are applied, default to showing weekly records
    $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
}

// Filter by MSISDN if provided
if ($case_id = $request->input('case_id')) {
    $query->where('case_id', $case_id);
}

// Filter by Case ID if provided
if ($site_name = $request->input('site_name')) {
    $query->where('site_name', $site_name);
}

// Count the tickets based on the filtered query
$closedTickets = $query->count();

// Get the filtered tickets
$tickets = $query->get(); // Get the results

// (Optional) Add time remaining calculations if required
foreach ($tickets as $ticket) {
    if ($ticket->faulty_type) {
        $currentTime = Carbon::now();
        $createdTime = Carbon::parse($ticket->created_at);
        $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
        $serviceLevelInMinutes = $ttrInHours * 60;
        $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
        $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes;
        if ($timeRemainingInMinutes < 0) {
            $timeRemainingInMinutes = 0;
        }
        $hoursRemaining = floor($timeRemainingInMinutes / 60);
        $minutesRemaining = $timeRemainingInMinutes % 60;
        $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
        $ticket->time_remaining = [
            'hours' => $hoursRemaining,
            'minutes' => $minutesRemaining,
            'totalMinutes' => $timeRemainingInMinutes,
            'percentage' => $percentageRemaining,
            'totalHours' => $ttrInHours
        ];
    } else {
        $ticket->time_remaining = [
            'hours' => 0,
            'minutes' => 0,
            'totalMinutes' => 0,
            'percentage' => 0,
            'totalHours' => 0
        ];
    }
}


// Permissions and Inbox Tickets Calculation (No Change)
$user = auth()->user();
$permissions = $user->user_group->permissions;
$userId = auth()->id();
$inboxTickets = ticket_resolutions::selectRaw('
    MAX(id) as id, 
    ticket_id, 
    MAX(created_at) as created_at, 
    MAX(opened) as opened'
)
->whereHas('tickets', function($query) use ($userId) {
    $query->where('user_id', $userId)
          ->where('closed', 'no')
          ->where('opened', 'no'); // Only include records where closed is 'no'
})
->groupBy('ticket_id')  // Group by ticket_id
->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
->with(['tickets' => function ($query) {
    $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
}])
->count();

    
            return view('noc_tickets.NocClosedTickets', compact('permissions', 'inboxTickets', 'tickets', 'closedTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Noc Closed Tickets' . $e->getMessage());
    }

}
public function viewEscalatedNocTicket(Request $request)
{
    
        try{

        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count(); 
    $tickets = noc_ticket_tbl::where('ticket_status', 'inprogress')
    ->where('escalation_status', 'open')
    ->orderBy('created_at', 'desc')
    ->get();
    foreach ($tickets as $ticket) {
        if ($ticket->faulty_type) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
            $serviceLevelInMinutes = $ttrInHours * 60; 
            $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
            $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
            if ($timeRemainingInMinutes < 0) {
                $timeRemainingInMinutes = 0;
            }
            $hoursRemaining = floor($timeRemainingInMinutes / 60);
            $minutesRemaining = $timeRemainingInMinutes % 60;
            $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
            $ticket->time_remaining = [
                'hours' => $hoursRemaining,
                'minutes' => $minutesRemaining,
                'totalMinutes' => $timeRemainingInMinutes,
                'percentage' => $percentageRemaining,
                'totalHours' => $ttrInHours
            ];
        } else {
            $ticket->time_remaining = [
                'hours' => 0,
                'minutes' => 0,
                'totalMinutes' => 0,
                'percentage' => 0,
                'totalHours' => 0
            ];
        }
    }
   
   
    $allOpenTickets = noc_ticket_tbl::where('ticket_status', 'inprogress')
    ->where('escalation_status', 'open')
  ->count();
    $user_group = User::with('user_group')
                ->whereHas('user_group', function($query) {
                    $query->where('group_name', 'Noc field Engineer'); // Adjust casing if necessary
                })
                ->where('status', 'active')
                ->get(); 
        return view('noc_tickets.NocEscalatedTickets', compact('permissions','inboxTickets','tickets','allOpenTickets', 'user_group'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Noc Main Report' . $e->getMessage());
    }

}
public function ShowNocEscalated($id)
{
        try{
        $ticket = noc_ticket_tbl::find($id);
        $resolutions = noc_resolutions::where('ticket_id', $ticket->id)->with('user')->get(); 
        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count(); 
    if ($ticket->faulty_type) {
        $currentTime = Carbon::now();
        $createdTime = Carbon::parse($ticket->created_at);
        $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
        $serviceLevelInMinutes = $ttrInHours * 60; 
        $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
        $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
        if ($timeRemainingInMinutes < 0) {
            $timeRemainingInMinutes = 0;
        }
        $hoursRemaining = floor($timeRemainingInMinutes / 60);
        $minutesRemaining = $timeRemainingInMinutes % 60;
        $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
        $ticket->time_remaining = [
            'hours' => $hoursRemaining,
            'minutes' => $minutesRemaining,
            'totalMinutes' => $timeRemainingInMinutes,
            'percentage' => $percentageRemaining,
            'totalHours' => $ttrInHours
        ];
    } else {
        $ticket->time_remaining = [
            'hours' => 0,
            'minutes' => 0,
            'totalMinutes' => 0,
            'percentage' => 0,
            'totalHours' => 0
        ];
    }

        return view('noc_tickets.ShowNocEscalated', compact('permissions','inboxTickets','ticket','resolutions'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load All Noc tickete' . $e->getMessage());
    }
}
public function UpdateNocEscalated(Request $request, $id)
{
    try{
        $validator = Validator::make($request->all(), [
            'resolution_remarks' => 'required|string|max:500',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = noc_ticket_tbl::findOrFail($id);

        $action = $request->input('action');
        if ($action == 'resolve') {
            $nocResolution = noc_resolutions::create([
             'ticket_id' => $id, 
             'user_id' => Auth::id(), 
             'resolution_remarks' => $request->resolution_remarks,
             'opened' => 'updated',
             'closed' => 'updated',
         ]);
        } 
        elseif ($action == 'closed') {
            $ticket->ticket_status = 'closed';
            $closed_date = Carbon::now();
            $ticket->closed_by = Auth::user()->id;
            $ticket->closed_date = $closed_date;
            $time_taken = round($ticket->created_at->diffInHours($ticket->closed_date, true), 2);
            $root_cause = $request->input('root_cause');
            $ticket->root_cause = $root_cause;
     // Calculate outage duration in days
     $fault_occurrence_time = $ticket->fault_occurrence_time; // Assuming this is a valid date
     $outage_duration = $fault_occurrence_time 
         ? round($ticket->closed_date->diffInDays($fault_occurrence_time, true), 2) 
         : 0;
     $ticket->outage_duration = $outage_duration;

         $nocResolution = noc_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'opened' => 'closed',
                'closed' => 'closed',
            ]);
            $ticket->time_taken = $time_taken;
            $ttr_in_hour = $ticket->faulty_type->ttr_in_hour;

            if ($time_taken <= $ttr_in_hour) {
                $ticket->sla_compliance = 'Within Time';
            } else {
                $ticket->sla_compliance = 'Out of Time';
            }
          
        }
        

        // Handle file uploads if any attachments are present
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $ticket->attachments()->create([
                    'file_path' => $path,
                    'tickets_id' => $ticket->id,
                ]);
            }
        }
        $ticket->save();
    
        if ($action == 'resolve') {
            return redirect('/noc_tickets.NocEscalatedTickets')->with('success', 'Update Saved And Sent');
        }

        elseif ($action == 'closed') {
            return redirect('/noc_tickets.NocEscalatedTickets')->with('success', 'Ticket Closed successfully.');
        }}
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Close Noc Tickets. ' . $e->getMessage());
        } 
}
public function viewNocReport(Request $request)
{ ini_set('memory_limit', '10240M');
    try{

        $currentDate = Carbon::today(); // Set current date for daily filter
$startOfWeek = $currentDate->copy()->startOfWeek(); // Get the start of the current week
$endOfWeek = $currentDate->copy()->endOfWeek(); // Get the end of the current week

// Initialize the query builder with relations
$query = noc_ticket_tbl::with(['noc_assigned_tickets.engineer', 'noc_assigned_tickets.assigner'])
    ->whereIn('ticket_status', ['open', 'closed', 'inprogress']) // Filter by status
    ->orderBy('created_at', 'desc');

// Check if date filters are applied
if ($request->has('start_date') && $request->has('end_date')) {
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    // Apply the date filter with start_date and end_date from the request
    $query->whereDate('created_at', '>=', $request->start_date)
          ->whereDate('created_at', '<=', $request->end_date);
} else {
    // If no date filters are applied, default to showing weekly records
    $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
}

// Filter by MSISDN if provided
if ($case_id = $request->input('case_id')) {
    $query->where('case_id', $case_id);
}

// Filter by Case ID if provided
if ($site_name = $request->input('site_name')) {
    $query->where('site_name', $site_name);
}
if ($sla_id = $request->input('sla_id')) {
    $query->where('sla_id', $sla_id);
}
if ($ticket_status = $request->input('ticket_status')) {
    $query->where('ticket_status', $ticket_status);
}
// Count the tickets based on the filtered query
$allTickets = $query->count();

// Get the filtered tickets
$tickets = $query->get(); // Get the results

// (Optional) Add time remaining calculations if required
foreach ($tickets as $ticket) {
    if ($ticket->faulty_type) {
        $currentTime = Carbon::now();
        $createdTime = Carbon::parse($ticket->created_at);
        $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
        $serviceLevelInMinutes = $ttrInHours * 60;
        $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
        $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes;
        if ($timeRemainingInMinutes < 0) {
            $timeRemainingInMinutes = 0;
        }
        $hoursRemaining = floor($timeRemainingInMinutes / 60);
        $minutesRemaining = $timeRemainingInMinutes % 60;
        $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
        $ticket->time_remaining = [
            'hours' => $hoursRemaining,
            'minutes' => $minutesRemaining,
            'totalMinutes' => $timeRemainingInMinutes,
            'percentage' => $percentageRemaining,
            'totalHours' => $ttrInHours
        ];
    } else {
        $ticket->time_remaining = [
            'hours' => 0,
            'minutes' => 0,
            'totalMinutes' => 0,
            'percentage' => 0,
            'totalHours' => 0
        ];
    }
}


// Permissions and Inbox Tickets Calculation (No Change)
$user = auth()->user();
$permissions = $user->user_group->permissions;
$userId = auth()->id();
$inboxTickets = ticket_resolutions::selectRaw('
    MAX(id) as id, 
    ticket_id, 
    MAX(created_at) as created_at, 
    MAX(opened) as opened'
)
->whereHas('tickets', function($query) use ($userId) {
    $query->where('user_id', $userId)
          ->where('closed', 'no')
          ->where('opened', 'no'); // Only include records where closed is 'no'
})
->groupBy('ticket_id')  // Group by ticket_id
->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
->with(['tickets' => function ($query) {
    $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
}])
->count();
    
           $faults = fault_type_tbl:: get();

        return view('noc_tickets.NocReport', compact('permissions','inboxTickets','tickets','allTickets', 'faults'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Noc Main Report' . $e->getMessage());
    }

}
public function shoAllTickets(Request $request,  $id)
{
    
        try{
        $ticket = noc_ticket_tbl::find($id);
        $resolutions = noc_resolutions::where('ticket_id', $ticket->id)->with('user')->get(); 
        $user = auth()->user();
        $permissions = $user->user_group->permissions;
        $userId = auth()->id();
        $inboxTickets = ticket_resolutions::selectRaw('
        MAX(id) as id, 
        ticket_id, 
        MAX(created_at) as created_at, 
        MAX(opened) as opened'
    )
    ->whereHas('tickets', function($query) use ($userId) {
        // Ensure the ticket is associated with the authenticated user
        $query->where('user_id', $userId)
              ->where('closed', 'no')
              ->where('opened', 'no'); // Only include records where closed is 'no'
    })
    ->groupBy('ticket_id')  // Group by ticket_id
    ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
    ->with(['tickets' => function ($query) {
        $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
    }])
    ->count(); 
    if ($ticket->faulty_type) {
        $currentTime = Carbon::now();
        $createdTime = Carbon::parse($ticket->created_at);
        $ttrInHours = (float) $ticket->faulty_type->ttr_in_hour;
        $serviceLevelInMinutes = $ttrInHours * 60; 
        $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
        $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes ;
        if ($timeRemainingInMinutes < 0) {
            $timeRemainingInMinutes = 0;
        }
        $hoursRemaining = floor($timeRemainingInMinutes / 60);
        $minutesRemaining = $timeRemainingInMinutes % 60;
        $percentageRemaining = ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100;
        $ticket->time_remaining = [
            'hours' => $hoursRemaining,
            'minutes' => $minutesRemaining,
            'totalMinutes' => $timeRemainingInMinutes,
            'percentage' => $percentageRemaining,
            'totalHours' => $ttrInHours
        ];
    } else {
        $ticket->time_remaining = [
            'hours' => 0,
            'minutes' => 0,
            'totalMinutes' => 0,
            'percentage' => 0,
            'totalHours' => 0
        ];
    }

        return view('noc_tickets.showAllTickets', compact('permissions','inboxTickets','ticket','resolutions'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load All Noc tickete' . $e->getMessage());
    }

}
   
}
