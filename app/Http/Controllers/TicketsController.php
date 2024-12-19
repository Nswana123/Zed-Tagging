<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Events\TicketEscalated;
use App\Mail\EscalationNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\user_group;
use App\Models\permissions;
use App\Models\role_permissions;
use App\Models\User;
use App\Models\ticket_sla;
use App\Models\ticket_category;
use App\Models\tickets;
use App\Models\attachment;
use App\Models\user_tickets;
use App\Models\assigned_tickets;
use App\Models\ticket_resolutions;
use App\Models\int_attachments;
use App\Models\int_ticket_resolutions;
use App\Models\int_tickets;
use App\Models\int_user_tickets;
use App\Models\devices;
use App\Models\products;
use App\Models\customer_locations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class TicketsController extends Controller
{
    //
    public function viewCallTag()
    {
        try {
            $user = auth()->user();
            $permissions = $user->user_group->permissions;
            return view('ticketing.call-tagging', compact('permissions'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Call Tags ' . $e->getMessage());
        }
    }
    public function getComplaintDetails(Request $request)
{
    if ($request->ajax()) {
        $categoryName = $request->input('category_name');
        $categoryType = $request->input('category_type');

        // Fetch matching categories based on category_name and category_type
        $matchingCategories = ticket_category::where('category_name', $categoryName)
                             ->where('category_type', $categoryType)
                             ->get(['id', 'category_detail']);  // Specify the fields to return

        return response()->json($matchingCategories);
    }
}
    public function viewTicketing(Request $request)
    {
        
            try{
            $ticket_cat = ticket_category::distinct()->get(['category_name']);
            $ticket_cate = ticket_category::whereNotNull('category_type')
            ->distinct()
            ->get(['category_type']);
            
            if ($request->has('msisd') && strlen($request->input('msisd')) == 10) {
                $request->validate([
                    'msisd' => 'digits:10',
                ]);
    
                $msisdn = $request->input('msisd');
                $tickets = tickets::where('msisdn', $msisdn)->distinct()->first();
    
                if (!$tickets) {
                    return redirect()->back()->withErrors('No tickets found');
                }
    
    
                $tickets = [$tickets]; // Wrap the single ticket in an array for display
            } else {
                $tickets = []; // Set empty tickets if search is not valid
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
        $locations = customer_locations::whereNotNull('province')
        ->whereNotNull('town')
        ->orderBy('province')
        ->orderBy('town')
        ->orderBy('landmark')
        ->get();
    
    $devices = devices::whereNotNull('brand')
        ->orderBy('brand')
        ->orderBy('model')
        ->get();
        
        $products = products::all()->sortBy('product');
            return view('ticketing.Ticketing', compact('permissions','products','tickets','devices','locations','ticket_cat','ticket_cate','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load ticketing ' . $e->getMessage());
        }
    
    }
    public function customerProfile($msisdn)
    {
        // Fetch all tickets for the selected MSISDN
        $tickets = tickets::with(['ticket_category','products','User','ticket_category.ticket_sla'])
        ->where('msisdn', $msisdn)
        ->orderBy('created_at', 'desc')
        ->get();
        $profile = tickets::with('customerLocation')->get();

        // Get common values for fname, lname, title
        $fname = $profile->pluck('fname')->unique()->count() === 1 ? $profile->pluck('fname')->first() : 'Varies';
        $lname = $profile->pluck('lname')->unique()->count() === 1 ? $profile->pluck('lname')->first() : 'Varies';
        $title = $profile->pluck('title')->unique()->count() === 1 ? $profile->pluck('title')->first() : 'Varies';
        // Fetch location details (use the location from the first ticket, if available)
        $location = $profile->first() ? $profile->first()->location : null;

        foreach ($tickets as $ticket) {
            foreach ($tickets as $ticket) {
                if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                    $currentTime = Carbon::now();
                    $createdTime = Carbon::parse($ticket->created_at);
                    $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
    $profileCount = tickets::with(['ticket_category','User','ticket_category.ticket_sla'])
        ->where('msisdn', $msisdn)  ->count();
        return view('ticketing.customerProfile', compact('tickets','profile','fname', 'lname', 'title', 'profileCount', 'location','profileCount','permissions','inboxTickets', 'msisdn'));
    }
    public function createTicket(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),[
                'msisdn' => 'required|string|max:255',
                'primary_no' => 'nullable|string|max:255',
                'title' => 'nullable|in:Mr.,Mrs.,Ms.,Dr.,Prof.',
                'fname' => 'required|string|max:100',
                'lname' => 'nullable|string|max:100',
                'method_of_contact' => 'nullable|string',
                'contact' => 'nullable',
                'product_id' => 'nullable',
                'cat_id' => 'required|exists:ticket_category,id',
                'issue_description' => 'nullable|max:500',
                'duration_of_experience' => 'nullable|string|max:255',
                'issue_status' => 'nullable|string',
                'device_id' => 'nullable|max:255',
                'location_id' => 'nullable',
                'interaction_status' => 'required|in:Resolved,Escalated',
                'root_cause' => 'nullable',
                'action_taken' => 'nullable|array',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048' // Max size of 2MB per image
            ]);
            if ($validator->fails()) {
                \Log::error($validator->errors());
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
                            $locationId = $request->input('location_id');
                           if ($locationId === 'new_location') {
                            // Save new device to the `devices` table
                            $newLocation = customer_locations::create([
                                'province' => $request->input('province'),
                                    'town' => $request->input('town'),
                                    'landmark' => $request->input('landmark'),
                            ]);
                            $locationId = $newLocation->id; // Assign the new device ID to be saved
                        }

                            $deviceId = $request->input('device_id');
                           if ($deviceId === 'new_device') {
                            // Save new device to the `devices` table
                            $newDevice = devices::create([
                                'brand' => $request->input('brand'),
                                'model' => $request->input('model'),
                            ]);
                            $deviceId = $newDevice->id; // Assign the new device ID to be saved
                        }
            $case_id = $this->generateCaseId();
            $interaction_status = $request->input('interaction_status');
            $ticket_status = ($interaction_status === 'Resolved') ? 'closed' : 'open';

            $closed_by = null;
            $closed_date = null;
            $time_taken = 0.0;
            $ticket_age = null;
// Step 1: Create records in int_tickets and int_ticket_resolutions
$int_ticket = int_tickets::create([
    'case_id' => $case_id,
    'msisdn' => $request->msisdn,
    'primary_no' => $request->primary_no,
    'title' => $request->title,
    'fname' => $request->fname,
    'lname' => $request->lname,
    'method_of_contact'=>$request->method_of_contact,
    'contact'=>$request->contact,
    'product_id'=>$request->product_id,
    'cat_id' => $request->cat_id,
    'category_name' => $request->category_name,
    'category_detail' => $request->category_detail,
    'issue_description' => $request->issue_description,
    'duration_of_experience' => $request->duration_of_experience,
    'issue_status' => $request->issue_status,
    'device_id' => $deviceId,
   'location_id' => $locationId,
    'interaction_status' => $request->interaction_status,
    'root_cause' => $request->root_cause,
    'ticket_status' => $ticket_status,
    'action_taken' => $request->has('action_taken') ? json_encode($request->action_taken) : null,
    'user_id' => Auth::id(),
]);

$int_ticket_resolution = int_ticket_resolutions::create([
    'ticket_id' => $int_ticket->id, // Ticket ID from int_tickets
    'user_id' => Auth::id(),
    'opened' => 'initial',
    'closed' => 'initial',
]);

// Step 2: Transfer records from int_tickets to tickets and int_ticket_resolutions to ticket_resolutions
$ticket = tickets::create([
    'case_id' => $int_ticket->case_id,
    'msisdn' => $int_ticket->msisdn,
    'primary_no' => $int_ticket->primary_no,
    'title' => $int_ticket->title,
    'fname' => $int_ticket->fname,
    'lname' => $int_ticket->lname,
    'method_of_contact' => $int_ticket->method_of_contact,
    'contact' => $int_ticket->contact,
    'product_id' => $int_ticket->product_id,
    'cat_id' => $int_ticket->cat_id,
    'category_name' => $int_ticket->category_name,
    'category_detail' => $int_ticket->category_detail,
    'issue_description' => $int_ticket->issue_description,
    'duration_of_experience' => $int_ticket->duration_of_experience,
    'issue_status' => $int_ticket->issue_status,
    'device_id' => $int_ticket->device_id,
    'location_id' => $int_ticket->location_id,
    'interaction_status' => $int_ticket->interaction_status,
    'root_cause' => $int_ticket->root_cause,
    'ticket_status' => $int_ticket->ticket_status,
    'action_taken' => $int_ticket->action_taken,
    'user_id' => $int_ticket->user_id,
]);

ticket_resolutions::create([
    'ticket_id' => $ticket->id, // New ticket ID from tickets
    'user_id' => Auth::id(),
    'opened' => $int_ticket_resolution->opened,
    'closed' => $int_ticket_resolution->closed,
]);
$int_ticket->delete();
$int_ticket_resolution->delete();
    
// Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments', 'public');
                    $ticket->attachments()->create([
                        'file_path' => $path,
                        'tickets_id' => $ticket->id, 
                    ]);
                }
            }
            if ($interaction_status === 'Resolved') {
                $closed_date = Carbon::now();
                $closed_by = Auth::id();
                $time_taken = 0.0;
                $ticket_age = "Within Time";
    
                $ticket->update([
                    'closed_date' => $closed_date,
                    'time_taken' => $time_taken,
                    'closed_by' => $closed_by,
                    'ticket_age' => $ticket_age,
                ]);
            }
                   // Prepare SMS content based on interaction status
        $smsMessage = $interaction_status === 'Resolved'
        ? "Thank you for calling ZedMobile."
        : "Dear Customer, your case has been escalated. Ticket ID: {$ticket->case_id}";

    // Call the SMS sending function
    $this->sendSMS($request->msisdn, $smsMessage);

            // Redirect back with success message
            return redirect()->back()->with('success', 'Ticket created successfully! Ticket #: ' . $ticket->case_id)->with('ticket', $ticket);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to create Ticket. ' . $e->getMessage());
        }
    }
    private function sendSMS($recipient, $message)
{
    $apiUrl = 'https://172.28.14.2:9800/api/SubmitSMS/';
    $apiPayload = [
        'UId' => '100',
        'ApiKey' => 'eX3Q2vmmfgQNvCpJzbLeeNn9jBUn/ysfoHCxsEoR/g0=',
        'Recipient' => $recipient,
        'Message' => $message,
    ];

    try {
        $response = Http::withoutVerifying()->post($apiUrl, $apiPayload);

        if ($response->successful()) {
            \Log::info("SMS sent successfully to {$recipient}");
        } else {
            \Log::error("Failed to send SMS to {$recipient}. Response: " . $response->body());
        }
    } catch (\Exception $e) {
        \Log::error("Error sending SMS to {$recipient}: " . $e->getMessage());
    }
}
    private function generateCaseId()
    {
        try{$prefix = 'Zed'; 

            do {
                $numeric_id = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
                $dateTimeSuffix = date('YmdHi');
                $case_id = $prefix . $numeric_id . $dateTimeSuffix;
            
            } while (tickets::where('case_id', $case_id)->exists());

        return $case_id;
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to generate Ticket id. ' . $e->getMessage());
    }
}
public function msisdn(Request $request)
{
    try {
        $msisdn = $request->input('msisdn');

        // Query the tickets table to find a ticket by MSISDN
        $ticket = tickets::where('msisdn', $msisdn)->first();

        // Check if the MSISDN exists
        if ($ticket) {
            // Find all tickets related to the MSISDN
            $tickets = tickets::where('msisdn', $msisdn)
                               ->orderBy('created_at', 'desc')
                               ->with(['ticket_resolutions' => function ($query) {
                                $query->where('closed', 'final');
                            }])
                               ->take(3)  // Fetch the latest 3 tickets
                               ->get();

            // Return the found data
            return response()->json([
                'status' => 'found',
                'data' => [
                    'primary_no' => $ticket->primary_no,
                    'title' => $ticket->title,
                    'fname' => $ticket->fname,
                    'lname' => $ticket->lname
                ],
                'tickets' => $tickets // Include tickets for the pop-up
            ]);
        }

        // If no ticket is found, return a 'not_found' status
        return response()->json([
            'status' => 'not_found',
            'message' => 'No data available for this MSISDN.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve data. ' . $e->getMessage()
        ], 500);
    }
}
public function viewOpenTicket(Request $request)
{
        try{
            $allOpenTickets =tickets::where('ticket_status','open')
            ->where('claim_status','open')->count();
            $tickets = tickets::with(['ticket_category','products','User','ticket_category.ticket_sla'])
            ->where('ticket_status','open')
            ->where('claim_status','open')
            ->orderBy('created_at','desc')
            ->get();
        
            foreach ($tickets as $ticket) {
                foreach ($tickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        return view('ticketing.open-tickets', compact('permissions','inboxTickets','tickets','allOpenTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Open Tickets ' . $e->getMessage());
    }

}
public function editOpenTicket($id)
{
    try {
        $ticket = tickets::with(['ticket_category','products','device','customerLocation','ticket_category.ticket_sla','attachments'])->findOrFail($id);
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
            $serviceLevelInMinutes = $ttrInHours * 60; 
            $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
            $timeRemainingInMinutes = $timePassedInMinutes + $serviceLevelInMinutes;
            
            if ($timeRemainingInMinutes < 0) {
                $timeRemainingInMinutes = 0;
            }
        
            $hoursRemaining = floor($timeRemainingInMinutes / 60);
            $minutesRemaining = $timeRemainingInMinutes % 60;
            
            // Prevent division by zero and calculate percentage correctly
            $percentageRemaining = $serviceLevelInMinutes > 0 ? min(100, max(0, ($timeRemainingInMinutes / $serviceLevelInMinutes) * 100)) : 0;
        
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
        
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
        }
        $user = auth()->user();
    $permissions = $user->user_group->permissions;
    $inboxTickets = ticket_resolutions::where('user_id', $user->id)
    ->where('closed', 'no')
    ->where('opened', 'no')
->with(['tickets' => function ($query) {
$query->with(['ticket_category', 'User', 'claimer', 'ticket_category.ticket_sla']);
}])
->count();
        return view('ticketing.showOpenTicket', compact('ticket','permissions','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Edit Open tickets. ' . $e->getMessage());
    }
}
// claim

public function claimTicket(Request $request, $id)
{
    try {
        $ticket = tickets::findOrFail($id);
        if ($ticket->claim_status == 'open'){
        $ticket->claim_status = 'claimed';
        $ticket->ticket_status = 'inprogress';
        $ticket->save();

        $intUserTicket = new int_user_tickets([
            'user_id' => Auth::id(), // Use the user ID from the request
            'ticket_id' => $ticket->id,
        ]);

        $intUserTicket->save();
        User_tickets::create([
            'user_id' => $intUserTicket->user_id,
            'ticket_id' => $intUserTicket->ticket_id,
        ]);
        $intUserTicket->delete();
        return redirect()->back()->with('success', 'Ticket claimed successfully.');
    }

    return redirect()->back()->withErrors('Ticket cannot be claimed.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to claim tickets ' . $e->getMessage());
    }
}
// clained tickets
public function ClaimedTickets(Request $request)
{
        try{
            $allOpenTickets =tickets::where('ticket_status','inprogress')
            ->where('claim_status','claimed')
            ->whereNull('escalation_status')
            ->count();

            $tickets = tickets::with(['ticket_category','User','user_tickets.claimer','user_tickets.assigner','ticket_category.ticket_sla','ticket_resolutions'])
            ->where('ticket_status','inprogress')
            ->where('claim_status','claimed')
            ->whereNull('escalation_status')
            ->orderBy('created_at','desc')
            ->get();
           
            $currentUser = Auth::user();

            $claimedTickets = tickets::whereHas('user_tickets', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->where('ticket_status', 'inprogress')  
            ->where('claim_status', 'claimed') 
            ->whereNull('escalation_status')    
            ->orderBy('created_at', 'desc')        
            ->with('user_tickets.user')            
            ->get(); 
            
            $claimedCount = tickets::whereHas('user_tickets', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->where('ticket_status', 'inprogress')  
            ->where('claim_status', 'claimed') 
            ->whereNull('escalation_status')    
            ->count();

          

   

                foreach ($tickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
                foreach ($claimedTickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
                
                $user_groups = User::whereHas('user_group', function($query) {
                    $query->where('group_name', 'back office'); // Adjust casing if necessary
                })
                ->with('user_group')
                ->where('status', 'active')
                ->get();

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
        return view('ticketing.claimedTickets', compact('permissions','inboxTickets','user_groups','tickets','allOpenTickets','claimedTickets','claimedCount'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Open Tickets ' . $e->getMessage());
    }

}
// re assigned

public function reAssignTicket(Request $request)
{
  try{  $request->validate([
        'ticket_id' => 'required|exists:tickets,id',
        'user_id' => 'required|exists:users,id',
    ]);

      $userTicket = user_tickets::where('ticket_id', $request->ticket_id)->first();

    
      if ($userTicket) {
        // Save a new entry in int_user_tickets
        $intUserTicket = new int_user_tickets([
            'user_id' => $request->user_id, 
            'ticket_id' => $userTicket->ticket_id,
        ]);

        $intUserTicket->save(); // Save new int_user_tickets entry

        // Update the existing user_ticket entry
        $userTicket->user_id = $intUserTicket->user_id; // Update user_id
        $userTicket->save();
        $intUserTicket->delete();

    return redirect()->back()->with('success', 'Ticket Reassigned successfully!');
}
}
catch (\Exception $e) {
    return redirect()->back()->withErrors('Failed to reassign tickets. ' . $e->getMessage());
}
}
public function editClaimedTcikets($id)
{
    try {

      
        $ticket = tickets::with(['ticket_category','products', 'ticket_category.ticket_sla', 'attachments'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
    
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
        }
       
        $user_group = user_group::all();
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

        return view('ticketing.showClaimedTcikets', compact('ticket','permissions','resolutions','inboxTickets','user_group'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Edit Claimed tickets. ' . $e->getMessage());
    }
}
public function updateClaimedtickets(Request $request, $id)
{
    try{
        $validator = Validator::make($request->all(), [
            
            'resolution_remarks' => 'required|string|max:500',
            'action_taken' => 'array', // Ensure action_taken is an array
            'action_taken.*' => 'string|max:255', // Ensure each action is a string
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = tickets::findOrFail($id);
        $action = $request->input('action');
        if ($action == 'save') {
            $ticket->root_cause = $request->input('root_cause');
            $ticket->ticket_quality = $request->input('ticket_quality');
            
           // Save to int_ticket_resolutions first
           $intResolution = int_ticket_resolutions::create([
            'ticket_id' => $id, 
            'user_id' => Auth::id(), 
            'resolution_remarks' => $request->resolution_remarks,
        ]);

        $resolution = ticket_resolutions::create([
            'ticket_id' => $intResolution->ticket_id,
            'user_id' => $intResolution->user_id,
            'resolution_remarks' => $intResolution->resolution_remarks,
            'opened' => 'no',
            'closed' => 'no',
        ]);
   $intResolution->delete();
        } 
        if ($action == 'comment') {
            $ticket->root_cause = $request->input('root_cause');
            $ticket->ticket_quality = $request->input('ticket_quality');
            
           // Save to int_ticket_resolutions first
           $intResolution = int_ticket_resolutions::create([
            'ticket_id' => $id, 
            'user_id' => Auth::id(), 
            'resolution_remarks' => $request->resolution_remarks,
        ]);

        $resolution = ticket_resolutions::create([
            'ticket_id' => $intResolution->ticket_id,
            'user_id' => $intResolution->user_id,
            'resolution_remarks' => $intResolution->resolution_remarks,
            'opened' => 'comment',
            'closed' => 'comment',
        ]);
   $intResolution->delete();
        } 
        elseif ($action == 'refund') {
            $ticket->refund = 'yes';
            $ticket->refund_status = 'not_refunded';
            $ticket->root_cause = $request->input('root_cause');
            $ticket->ticket_quality = $request->input('ticket_quality');

            $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'opened' => 'refund',
                'closed' => 'refund',
            ]);

            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'opened' => 'refund',
                'closed' => 'refund',
            ]);

           $intResolution->delete();
        }
        elseif ($action == 'closed') {
            $ticket->ticket_status = 'closed';
            $ticket->root_cause = $request->input('root_cause');
            $ticket->ticket_quality = $request->input('ticket_quality');
            $closed_date = Carbon::now();
            $ticket->closed_by = Auth::user()->id;
            $ticket->closed_date = $closed_date;
            $ticket->escalation_group = Auth::user()->group_id;
            $time_taken = round($ticket->created_at->diffInHours($ticket->closed_date, true), 2);

         $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'opened' => 'final',
                'closed' => 'final',
            ]);
            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'opened' => 'final',
                'closed' => 'final',
            ]);
           $intResolution->delete();
            $ticket->time_taken = $time_taken;
            $ttr_in_hour = $ticket->ticket_category->ticket_sla->ttr_in_hour;

            if ($time_taken <= $ttr_in_hour) {
                $ticket->ticket_age = 'Within Time';
            } else {
                $ticket->ticket_age = 'Out of Time';
            }
          
        }
        $ticket->action_taken = json_encode($request->action_taken); // Update action_taken
        

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

        if ($action == 'escalate') {
            return redirect('/ticketing.claimedTickets')->with('success', 'Ticket escalated successfully.');
        } 
        elseif ($action == 'refund') {
            return redirect('/ticketing.claimedTickets')->with('success', 'Added to refunds successfully.');
        }
        elseif ($action == 'save') {
            return redirect('/ticketing.claimedTickets')->with('success', 'ticket sent to the Agent');
        }
        elseif ($action == 'comment') {
            return redirect('/ticketing.claimedTickets')->with('success', 'Comment Saved successfully.');
        }
        elseif ($action == 'closed') {
            return redirect('/ticketing.claimedTickets')->with('success', 'Ticket Closed successfully.');
        }}
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load ticket. ' . $e->getMessage());
        } 
}
public function escalationGroup(Request $request)
{
    try {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'escalation_group' => 'required|exists:user_group,id',
        ]);

        // Find the ticket
        $ticket = tickets::find($request->ticket_id);

        if ($ticket) {
            // Update escalation status and group
            $ticket->escalation_status = 'open';
            $ticket->escalation_group = $request->escalation_group;
            $ticket->save();

            // Log the escalation in resolutions
            $resolution = new int_ticket_resolutions([
                'ticket_id' => $request->ticket_id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks ?? 'No remarks',
                'opened' => 'escalated',
                'closed' => 'escalated',
            ]);
            $resolution->save();

            // Send emails to all users in the escalation group
            $users = User::where('group_id', $request->escalation_group)->get();

              // Dispatch the real-time event to the group
              event(new TicketEscalated($ticket, $users));
            foreach ($users as $user) {
                Mail::to($user->email)->send(new EscalationNotification($ticket, $user));
            }

            return redirect('/ticketing.claimedTickets')->with('success', 'Ticket escalated and notifications sent!');
        }
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to escalate ticket. ' . $e->getMessage());
    }
}

public function Inbox(Request $request)
{
        try{
           
            $userId = auth()->id();  // Get the current user’s ID
            $tickets = ticket_resolutions::selectRaw('
            MAX(id) as id, 
            ticket_id, 
            MAX(created_at) as created_at, 
            MAX(opened) as opened'
        )
        ->whereHas('tickets', function($query) use ($userId) {
            // Ensure the ticket is associated with the authenticated user
            $query->where('user_id', $userId)
                  ->where('closed', 'no');  // Only include records where closed is 'no'
        })
        ->groupBy('ticket_id')  // Group by ticket_id
        ->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
        ->with(['tickets' => function ($query) {
            $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
        }])
        ->get();
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

$openinboxTickets = ticket_resolutions::selectRaw('
MAX(id) as id, 
ticket_id, 
MAX(created_at) as created_at, 
MAX(opened) as opened'
)
->whereHas('tickets', function($query) use ($userId) {
// Ensure the ticket is associated with the authenticated user
$query->where('user_id', $userId)
      ->where('closed', 'no');  // Only include records where closed is 'no'
})
->groupBy('ticket_id')  // Group by ticket_id
->orderByRaw('MAX(created_at) desc')  // Order by MAX(created_at)
->with(['tickets' => function ($query) {
$query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
}])
->count(); 
$locations = customer_locations::whereNotNull('province')
->whereNotNull('town')
->orderBy('province')
->orderBy('town')
->orderBy('landmark')
->get();

$devices = devices::whereNotNull('brand')
->orderBy('brand')
->orderBy('model')
->get();
        
            foreach ($tickets as $ticket) {
                foreach ($tickets as $ticket) {
                    if ($ticket->tickets->ticket_category && $ticket->tickets->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->tickets->created_at);
                        $ttrInHours = (float) $ticket->tickets->ticket_category->ticket_sla->ttr_in_hour;
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
        return view('ticketing.inbox', compact('permissions','tickets','inboxTickets','openinboxTickets','locations','devices'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Inbox Tickets ' . $e->getMessage());
    }

}
public function showInboxTickets($id)
{
    try {
        $ticket = tickets::with(['ticket_category','products','ticket_category.ticket_sla','attachments'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
        foreach ($resolutions as $resolution) {
            $resolution->update(['opened' => 'opened']);  // Update each resolution
        }
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
     
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
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
$locations = customer_locations::whereNotNull('province')
->whereNotNull('town')
->orderBy('province')
->orderBy('town')
->orderBy('landmark')
->get();

$devices = devices::whereNotNull('brand')
->orderBy('brand')
->orderBy('model')
->get(); 
        return view('ticketing.showInboxTickets', compact('ticket','permissions','resolutions','inboxTickets','locations','devices'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Open Inbox tickets. ' . $e->getMessage());
    }
}
public function updateInboxtickets(Request $request, $id)
{
    try{
        $validator = Validator::make($request->all(), [
            
           
            'resolution_remarks' => 'nullable|max:500',
            'action_taken' => 'array', // Ensure action_taken is an array
            'action_taken.*' => 'string|max:255', // Ensure each action is a string
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'device_id' => 'nullable|max:255',
            'location_id' => 'nullable'
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = tickets::findOrFail($id);
        $action = $request->input('action');
        if ($action == 'save') {
            $locationId = $request->input('location_id');
            if ($locationId === 'new_location') {
             // Save new device to the `devices` table
             $newLocation = customer_locations::create([
                 'province' => $request->input('province'),
                     'town' => $request->input('town'),
                     'landmark' => $request->input('landmark'),
             ]);
             $locationId = $newLocation->id; // Assign the new device ID to be saved
         }

             $deviceId = $request->input('device_id');
            if ($deviceId === 'new_device') {
             // Save new device to the `devices` table
             $newDevice = devices::create([
                 'brand' => $request->input('brand'),
                 'model' => $request->input('model'),
             ]);
             $deviceId = $newDevice->id; // Assign the new device ID to be saved
         }
            $ticket->root_cause = $request->input('root_cause');
            $ticket->device_id = $deviceId;
            $ticket->location_id = $locationId;
         
        DB::transaction(function () use ($request, $id) {
            // Create a new ticket resolution
            $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'closed' => 'closed',
            ]);
            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'closed' => 'closed',
            ]);
           $intResolution->delete();
    
            // Update other resolutions with the same ticket_id to 'closed' = 'yes'
            ticket_resolutions::where('ticket_id', $id)
                ->where('id', '!=', $id) // Exclude the newly created resolution if you want
                ->update(['closed' => 'closed']);
        });
        $ticket->action_taken = json_encode($request->action_taken); // Update action_taken
        

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

       
        if ($action == 'save') {
            return redirect('/ticketing.inbox')->with('success', 'Responded successfully.');
        }
    }
}
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load ticket. ' . $e->getMessage());
        } 
}
//unclaimed Tickets
public function unClaimedTickets(Request $request)
{
        try{
            $allOpenTickets =tickets::where('ticket_status','open')
            ->where('claim_status','unclaimed')
            ->count();

            $tickets = tickets::with(['ticket_category','User','claimer','ticket_category.ticket_sla','ticket_resolutions'])
            ->where('ticket_status','open')
            ->where('claim_status','unclaimed')
            ->orderBy('created_at','desc')
            ->get();
                foreach ($tickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
               
                $user_group = User::with('user_group')
                ->whereHas('user_group', function($query) {
                    $query->where('group_name', 'back office'); // Adjust casing if necessary
                })
                ->where('status', 'active')
                ->get(); 
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

        return view('ticketing.unclaimedTickets', compact('permissions','inboxTickets','user_group','tickets','allOpenTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load unclaimed Tickets ' . $e->getMessage());
    }

}

public function AssignTicket(Request $request)
{
  try{  
    $ticket = tickets::findOrFail($request->ticket_id);
    $request->validate([
    
    ]);
    $ticket->ticket_status = 'inprogress';
    $ticket->claim_status = 'claimed';
    $ticket->save();

    $intUserTicket = new int_user_tickets([
        'assigner_id' => Auth::id(), // Get the authenticated user ID
        'ticket_id' => $ticket->id,
        'assignment_status' => 'closed',
        'user_id' => $request->user_id,
    ]);
    $intUserTicket->save();
    user_tickets::create([
        'assigner_id' => $intUserTicket->assigner_id,
        'ticket_id' => $intUserTicket->ticket_id,
        'assignment_status' => 'closed',
        'user_id' => $intUserTicket->user_id,
    ]);

$intUserTicket->delete();

    return redirect()->back()->with('success', 'Ticket Assigned successfully!');

}
catch (\Exception $e) {
    return redirect()->back()->withErrors('Failed to Assign tickets. ' . $e->getMessage());
}
}
// closed Tickets
public function viewClosedTicket(Request $request)
{
    ini_set('memory_limit', '10240M');
        try{

            $today = Carbon::today();
            $startDate = $request->input('start_date', $today);
            $endDate = $request->input('end_date', $today);
            $msisdn = $request->input('msisdn');
            $caseId = $request->input('case_id');
        
            // Query for tickets
            $query = tickets::with(['ticket_category','products','user','closedBy','ticket_category.ticket_sla'])
				->whereDate('closed_date', '>=', $startDate)
                            ->whereDate('closed_date', '<=', $endDate)
                            ->where('ticket_status', 'closed');
            if ($msisdn) {
                $query->where('msisdn', $msisdn);
            }
        
            if ($caseId) {
                $query->where('case_id', $caseId);
            }
            $allClosedTickets = $query->count();
            $tickets = $query->get();
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
        return view('ticketing.Closed-tickets', compact('permissions','inboxTickets','tickets','allClosedTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Closed Tickets ' . $e->getMessage());
    }

}
public function editClosedTicket($id)
{
    try {
        $ticket = tickets::with(['ticket_category','products','device','customerLocation','ticket_category.ticket_sla','attachments','closedBy'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
        foreach ($resolutions as $resolution) {
            $resolution->update(['opened' => 'opened']);  // Update each resolution
        }
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
        }
        $user = auth()->user();
    $permissions = $user->user_group->permissions;
    $inboxTickets = ticket_resolutions::selectRaw('
    MAX(id) as id, 
    ticket_id, 
    MAX(created_at) as created_at, 
    MAX(opened) as opened'
) 
->whereHas('user', function ($query) {
    $query->where('closed', 'no') 
          ->where('opened', 'no');
})
->groupBy('ticket_id') 
->with(['tickets' => function ($query) {
    $query->with(['ticket_category', 'User', 'claimer', 'ticket_category.ticket_sla']);
}])
->count(); 
        return view('ticketing.showClosedTicket', compact('ticket','permissions','resolutions','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Open Inbox tickets. ' . $e->getMessage());
    }
}
// All Tickets
public function viewallTicket(Request $request)
{
    ini_set('memory_limit', '10240M');
        try{

            // Start of the week (to default to current week if no dates are selected)
            $currentDate = Carbon::today(); // Set current date for daily filter

            // Initialize the query builder with relations
            $query = tickets::with(['ticket_category', 'products', 'user', 'closedBy', 'ticket_category.ticket_sla'])
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
                // If no date filters are applied, default to showing today's records only
                $query->whereDate('created_at', $currentDate);
            }
            
            // Filter by MSISDN if provided
            if ($msisdn = $request->input('msisdn')) {
                $query->where('msisdn', $msisdn);
            }
            
            // Filter by Case ID if provided
            if ($caseId = $request->input('case_id')) {
                $query->where('case_id', $caseId);
            }
            
            // Count the tickets based on the filtered query
            $allTickets = $query->count();
            
            // Get the filtered tickets
            $tickets = $query->get(); // Paginate the results (10 per page)
            
              /*  foreach ($tickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
                        $serviceLevelInMinutes = $ttrInHours * 60; 
                        $timePassedInMinutes = $currentTime->diffInMinutes($createdTime);
                        $timeRemainingInMinutes = $serviceLevelInMinutes - $timePassedInMinutes;
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
      */
            
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
        return view('ticketing.all-tickets', compact('permissions','inboxTickets','tickets','allTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Closed Tickets ' . $e->getMessage());
    }

}
public function editallTicket($id)
{
    try {
        $ticket = tickets::with(['ticket_category','device','products','customerLocation','ticket_category.ticket_sla','attachments','closedBy'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
        foreach ($resolutions as $resolution) {
            $resolution->update(['opened' => 'opened']);  // Update each resolution
        }
      /*  if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        }*/
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
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
        return view('ticketing.showallTicket', compact('ticket','permissions','resolutions','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Open Inbox tickets. ' . $e->getMessage());
    }
}
public function allRefundList(Request $request)
{
    ini_set('memory_limit', '10240M');
        try{

            $allrefunds = tickets::where('refund','yes')->count();
            $refunded = tickets::where('refund_status', 'refunded')->count();
            $Notrefunded = tickets::where('refund_status', 'not_refunded')->count();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $startOfMonth;
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : $endOfMonth;
            $msisdn = $request->input('msisdn');
            $caseId = $request->input('case_id');
            $refund_status = $request->input('refund_status');
        
            // Query for tickets
            $query = tickets::with(['ticket_category','user','closedBy','ticket_category.ticket_sla'])
			->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                            ->where('refund', 'yes')
                            ->where('refund_status', 'not_refunded');
            if ($msisdn) {
                $query->where('msisdn', $msisdn);
            }
        
            if ($caseId) {
                $query->where('case_id', $caseId);
            }
            if ($refund_status) {
                $query->where('refund_status', $refund_status);
            }
            $tickets = $query->get();
        
            foreach ($tickets as $ticket) {
                foreach ($tickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        return view('ticketing.refundList', compact('permissions','inboxTickets','tickets','allrefunds','refunded','Notrefunded'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Closed Tickets ' . $e->getMessage());
    }

}
public function showRefundList($id)
{
    try {
        $ticket = tickets::with(['ticket_category','products','ticket_category.ticket_sla','attachments','closedBy'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
        foreach ($resolutions as $resolution) {
            $resolution->update(['opened' => 'opened']);  // Update each resolution
        }
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
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
        return view('ticketing.showRefundList', compact('ticket','permissions','resolutions','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Open Inbox tickets. ' . $e->getMessage());
    }
}
public function updateRefund(Request $request, $id)
{
    ini_set('memory_limit', '10240M');
    try{
        $validator = Validator::make($request->all(), [
            
       
            'resolution_remarks' => 'string|max:500',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = tickets::findOrFail($id);
        $action = $request->input('action');
        if ($action == 'closed') {
            $ticket->ticket_status = 'closed';
            $ticket->root_cause = $request->input('root_cause');
            $ticket->refund_status = 'refunded';
            $closed_date = Carbon::now();
            $ticket->closed_by = Auth::user()->id;
            $ticket->closed_date = $closed_date;
            $time_taken = round($ticket->created_at->diffInHours($ticket->closed_date, true), 2);

            $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'opened' => 'refund',
                'closed' => 'refund',
            ]);

            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'opened' => 'refund',
                'closed' => 'refund',
            ]);

           $intResolution->delete();
            $ticket->time_taken = $time_taken;
            $ttr_in_hour = $ticket->ticket_category->ticket_sla->ttr_in_hour;

            if ($time_taken <= $ttr_in_hour) {
                $ticket->ticket_age = 'Within Time';
            } else {
                $ticket->ticket_age = 'Out of Time';
            }
          
        }
        $ticket->save();

      if ($action == 'closed') {
            return redirect('/ticketing.refundList')->with('success', 'Ticket Closed successfully.');
        }}
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load ticket. ' . $e->getMessage());
        } 
}
public function EscalationTickets(Request $request)
{
        try{ 
           
            $currentUser = Auth::user();

            $tickets = tickets::join('users', 'users.group_id', '=', 'tickets.escalation_group')
            ->join('user_group', 'user_group.id', '=', 'users.group_id')  // Use 'user_groups' plural (based on table name convention)
            ->where('tickets.ticket_status', 'inprogress')
            ->where('tickets.claim_status', 'claimed')
            ->where('tickets.escalation_status', 'open')
            ->where('user_group.id', $currentUser->group_id)  // Ensure the group_id matches
            ->orderBy('tickets.created_at', 'desc')
            ->select('tickets.*')  // Select the necessary fields
            ->distinct()  // Ensure no duplicate rows
            ->get();
            $EscalatedCount = tickets::join('users', 'users.group_id', '=', 'tickets.escalation_group')
            ->where('tickets.ticket_status', 'inprogress')
            ->where('tickets.claim_status', 'claimed')
            ->where('tickets.escalation_status', 'open')
            ->where('users.group_id', $currentUser->group_id)  // Match group_id with current user's group
            ->distinct('tickets.id')  // Ensure no duplicate tickets by ID
            ->count('tickets.id');
             $allEscalated = tickets::with(['ticket_category','ticket_resolutions', 'User', 'claimer', 'ticket_category.ticket_sla'])
             ->where('escalation_status', 'open')->get();
             $allEscalatedCount = tickets::where('escalation_status', 'open')->count(); 
            foreach ($tickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
                foreach ($allEscalated as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        return view('ticketing.EscalatedTickets', compact('permissions','inboxTickets','tickets','EscalatedCount','allEscalated','allEscalatedCount'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Escalated Tickets ' . $e->getMessage());
    }

}
public function showEscalatedTickets($id)
{
    try {
        $ticket = tickets::with(['ticket_category','products','ticket_category.ticket_sla','attachments','closedBy'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
        foreach ($resolutions as $resolution) {
            $resolution->update(['opened' => 'opened']);  // Update each resolution
        }
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
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
        return view('ticketing.showEscalatedTickets', compact('ticket','permissions','resolutions','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Open Inbox tickets. ' . $e->getMessage());
    }
}
public function updateEscalatedTicket(Request $request, $id)
{
    try{
        $validator = Validator::make($request->all(), [
            
           
            'resolution_remarks' => 'string|max:500',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = tickets::findOrFail($id);
        $action = $request->input('action');
        if ($action == 'closed') {
            $ticket->escalation_status = 'resolved';
          
            $ticket->ticket_status = 'closed';
            $ticket->root_cause = $request->input('root_cause');
            $ticket->ticket_quality = $request->input('ticket_quality');
            $closed_date = Carbon::now();
            $ticket->closed_by = Auth::user()->id;
            $ticket->closed_date = $closed_date;
            $time_taken = round($ticket->created_at->diffInHours($ticket->closed_date, true), 2);
            $ticket->time_taken = $time_taken;
            $ttr_in_hour = $ticket->ticket_category->ticket_sla->ttr_in_hour;

            if ($time_taken <= $ttr_in_hour) {
                $ticket->ticket_age = 'Within Time';
            } else {
                $ticket->ticket_age = 'Out of Time';
            }
         $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'opened' => 'final',
                'closed' => 'final',
            ]);
            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'opened' => 'final',
                'closed' => 'final',]);

           $intResolution->delete();
          
        }  
        elseif ($action == 'save') {
            $ticket->root_cause = $request->input('root_cause');
            $ticket->ticket_quality = $request->input('ticket_quality');
            
            $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id, 
                'user_id' => Auth::id(), 
                'resolution_remarks' => $request->resolution_remarks,
            ]);
    
            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'opened' => 'no',
                'closed' => 'no',
            ]);
            $intResolution->delete();
        } elseif ($action == 'comment') {
            $ticket->root_cause = $request->input('root_cause');
            $ticket->ticket_quality = $request->input('ticket_quality');
            
            $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id, 
                'user_id' => Auth::id(), 
                'resolution_remarks' => $request->resolution_remarks,
            ]);
    
            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'opened' => 'comment',
                'closed' => 'comment',
            ]);
            $intResolution->delete();
        }  
        $ticket->save();

      if ($action == 'closed') {
            return redirect('/ticketing.escalated-Tickets')->with('success', 'Ticket Resolved successfully.');
        }
        elseif ($action == 'save') {
            return redirect('/ticketing.escalated-Tickets')->with('success', 'Ticket Sent to the Agent.');
        }
        elseif ($action == 'comment') {
            return redirect('/ticketing.escalated-Tickets')->with('success', 'Commented successfully.');
        }
    }
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load ticket. ' . $e->getMessage());
        } 
}
public function showInboTickets($id)
{
    try {
        $ticket = tickets::with(['ticket_category','products','ticket_category.ticket_sla','attachments'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
        foreach ($resolutions as $resolution) {
            $resolution->update(['opened' => 'opened']);  // Update each resolution
        }
        $locations = customer_locations::whereNotNull('province')
->whereNotNull('town')
->orderBy('province')
->orderBy('town')
->orderBy('landmark')
->get();

$devices = devices::whereNotNull('brand')
->orderBy('brand')
->orderBy('model')
->get();
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
     
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
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
        return view('ticketing.showInboTickets', compact('ticket','permissions','resolutions','inboxTickets','locations','devices'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Open Inbox tickets. ' . $e->getMessage());
    }
}
public function updateInbotickets(Request $request, $id)
{
    try{
        $validator = Validator::make($request->all(), [
           
            'resolution_remarks' => 'nullable|max:500',
            'action_taken' => 'array', // Ensure action_taken is an array
            'action_taken.*' => 'string|max:255', // Ensure each action is a string
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'location_id' => 'string|max:255',
            'device_id' => 'string|max:255'
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = tickets::findOrFail($id);
        $action = $request->input('action');
        if ($action == 'save') {
            $locationId = $request->input('location_id');
    $deviceId = $request->input('device_id');

    // Check if a new location is being created
    if ($locationId === 'new_location') {
        $newLocation = customer_locations::create([
            'province' => $request->input('province'),
            'town' => $request->input('town'),
            'landmark' => $request->input('landmark')
        ]);
        $locationId = $newLocation->id; // Assign the new location ID to be saved
    }

    // Check if a new device is being created
    if ($deviceId === 'new_device') {
        $newDevice = devices::create([
            'brand' => $request->input('brand'),
            'model' => $request->input('model')
        ]);
        $deviceId = $newDevice->id; // Assign the new device ID to be saved
    }

    // Update ticket with either the existing IDs or newly created ones
    $ticket->root_cause = $request->input('root_cause');
    $ticket->device_id = $deviceId ?? $ticket->device_id;
    $ticket->location_id = $locationId ?? $ticket->location_id;
         
        DB::transaction(function () use ($request, $id) {
            // Save to int_ticket_resolutions first
            $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id, 
                'user_id' => Auth::id(), 
                'resolution_remarks' => $request->resolution_remarks,
            ]);
    
            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'closed' => 'closed',
            ]);
        $intResolution->delete();
    
            // Update other resolutions with the same ticket_id to 'closed' = 'yes'
            ticket_resolutions::where('ticket_id', $id)
                ->where('id', '!=', $id) // Exclude the newly created resolution if you want
                ->update(['closed' => 'closed']);
        });
        $ticket->action_taken = json_encode($request->action_taken); // Update action_taken
        

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

       
        if ($action == 'save') {
            return redirect()->back()->with('success', 'Responded successfully.');
        }
    }
}
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load ticket. ' . $e->getMessage());
        } 
}
public function ResolvedTickets(Request $request)
{
        try{
            $ResolvedCount =tickets::where('escalation_status','resolved')
            ->count();

            $tickets = tickets::with(['ticket_category','User','claimer','ticket_category.ticket_sla','ticket_resolutions'])
            ->where('escalation_status','resolved')
            ->orderBy('created_at','desc')
            ->get();
            
                foreach ($tickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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

        return view('ticketing.ResolvedTickets', compact('permissions','inboxTickets','tickets','ResolvedCount'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Resolved Tickets ' . $e->getMessage());
    }

}
public function showResolvedTickets($id)
{
    try {
        $ticket = tickets::with(['ticket_category','products','ticket_category.ticket_sla','attachments','closedBy'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
        foreach ($resolutions as $resolution) {
            $resolution->update(['opened' => 'opened']);  // Update each resolution
        }
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
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
        return view('ticketing.showResolvedTickets', compact('ticket','permissions','resolutions','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Open Inbox tickets. ' . $e->getMessage());
    }
}
public function updateResolvedTicket(Request $request, $id)
{
    try{
        $validator = Validator::make($request->all(), [
            
            'resolution_remarks' => 'string|max:500',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }
    
    $ticket = tickets::findOrFail($id);
        $action = $request->input('action');
        if ($action == 'closed') {
            $ticket->escalation_status = 'closed';
            $intResolution = int_ticket_resolutions::create([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'resolution_remarks' => $request->resolution_remarks,
                'opened' => 'resolved',
                'closed' => 'resolved',
            ]);
            $resolution = ticket_resolutions::create([
                'ticket_id' => $intResolution->ticket_id,
                'user_id' => $intResolution->user_id,
                'resolution_remarks' => $intResolution->resolution_remarks,
                'opened' => 'resolved',
                'closed' => 'resolved',
            ]);
           $intResolution->delete();
        $ticket->save();
    }
      if ($action == 'closed') {
            return redirect('/ticketing.ResolvedTickets')->with('success', 'Ticket Closed successfully.');
        }}
        catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Resolved ticket. ' . $e->getMessage());
        } 
}

    // Method to handle search request
    public function search(Request $request)
    {
        // Validate the search input
        $request->validate([
            'msisdn' => 'required|digits:10',
        ]);

        $msisdn = $request->input('msisdn');

        // Query the tickets table to find MSISDN
        $tickets = Ticket::where('msisdn', $msisdn)->get();

        // If no tickets found, pass an empty result
        if ($tickets->isEmpty()) {
            return redirect()->back()->withErrors('No tickets found for the given MSISDN.');
        }

        // Return the view with the search results
        return view('tickets.search', compact('tickets'));
    }
// Ticket Quality
// All Tickets
public function ticketQuality(Request $request)
{
    ini_set('memory_limit', '10240M');
        try{
            $today = Carbon::today();
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $today;
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : $today;
          
            $TicketStatusId = $request->input('ticket_status');
            $interectionId = $request->input('interaction_status');
            $userId = $request->input('user_id');
            $ticketQuality = $request->input('ticket_quality');

            $query = tickets::with(['ticket_category', 'user', 'closedBy', 'ticket_category.ticket_sla'])
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereIn('ticket_status', ['open', 'closed', 'inprogress']);
            if ($TicketStatusId) {
                $query->where('ticket_status', $TicketStatusId);
            }
            if ($interectionId) {
                $query->where('interaction_status', $interectionId);
            }
            if ($userId) {
                $query->where('user_id', $userId);
            }
            if ($ticketQuality) {
                $query->where('ticket_quality', $ticketQuality);
            }
           
            $allTickets = $query->count();
            $QAtickets = $query->get();
                foreach ($QAtickets as $ticket) {
                    if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
                        $currentTime = Carbon::now();
                        $createdTime = Carbon::parse($ticket->created_at);
                        $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
    $group = User::with('user_group')
    ->whereHas('user_group', function($query) {
        $query->where('group_name', 'customer support'); // Adjust casing if necessary
    }) 
    ->get(); 
        return view('ticketing.ticket-quality', compact('permissions','inboxTickets','group','QAtickets','allTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Ticket Quality' . $e->getMessage());
    }

}
public function showQuality($id)
{
    try {
        $ticket = tickets::with(['ticket_category','products','device','customerLocation','ticket_category.ticket_sla','attachments','closedBy'])->findOrFail($id);
        $resolutions = ticket_resolutions::where('ticket_id', $ticket->id)->with('user')->get();  // Fetch resolutions with the user relation
        foreach ($resolutions as $resolution) {
            $resolution->update(['opened' => 'opened']);  // Update each resolution
        }
        if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla) {
            $currentTime = Carbon::now();
            $createdTime = Carbon::parse($ticket->created_at);
            $ttrInHours = (float) $ticket->ticket_category->ticket_sla->ttr_in_hour;
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
        if (is_string($ticket->action_taken)) {
            $ticket->action_taken = json_decode($ticket->action_taken, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ticket->action_taken = []; 
            }
        }
        if (!is_array($ticket->action_taken)) {
            $ticket->action_taken = [];
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
        return view('ticketing.showQuality', compact('ticket','permissions','resolutions','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to Open Ticket Quality. ' . $e->getMessage());
    }
}
public function updateQuality(request $request,$id){
    try{$ticket = tickets::findOrFail($id);
        $action = $request->input('action');

        if ($action == 'rate') {
            $ticket->ticket_quality = $request->input('ticket_quality');
            
        }
        
        $ticket->save();

        if ($action == 'rate') {
            return redirect('/ticketing.ticket-quality')->with('success', 'Ticket rated successfully.');
        } 
}
catch (\Exception $e) {
    return redirect()->back()->withErrors('Failed to rate Ticket ' . $e->getMessage());
}
}

//the end
    }
