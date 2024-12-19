<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
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
use App\Models\messages;
use App\Models\cust_messages;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
class CategoryController extends Controller
{
    //
    public function viewTicketSla()
    {
        try {
            $slas = ticket_sla::get();
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
            return view('category.ticket-sla', compact('permissions','slas','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load sla. ' . $e->getMessage());
        }
    }
   
    public function createSla(Request $request)
    {
        try{
        $validatedData = $request->validate([
            'priority' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'ttr_in_hour' => 'required|numeric|min:0', // Ensure TTR is a numeric value
        ]);
        ticket_sla::create([
            'priority' => $validatedData['priority'],
            'description' => $validatedData['description'],
            'ttr_in_hour' => $validatedData['ttr_in_hour'],
        ]);
        return redirect()->back()->with('success', 'SLA ticket created successfully!');
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load sla. ' . $e->getMessage());
    }
}
    public function DeleteTicketSla($id)
    {
        $ticket_sla = ticket_sla::findOrFail($id);
        $ticket_sla ->delete();
        return redirect()->route('category.ticket-sla')->with('success', 'Ticket Sla deleted successfully.');
    }
    public function editTicketSla($id)
    {
        try {
            $ticket_sla = ticket_sla::findOrFail($id);
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
            return view('category.editTicketSla', compact('ticket_sla','permissions','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to edit ticket sla. ' . $e->getMessage());
        }
    }
    public function updateTicketSla(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'priority' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'ttr_in_hour' => 'required|numeric|min:0',
            ]);
    
            $ticket_sla = ticket_sla::findOrFail($id);
            $ticket_sla->update([
                'priority' => $validatedData['priority'],
                'description' => $validatedData['description'],
                'ttr_in_hour' => $validatedData['ttr_in_hour'],
            ]);
   
            return redirect()->route('category.ticket-sla')->with('success', 'ticket SLA updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to update ticket Sla. ' . $e->getMessage());
        }
    }
    // category
    public function viewTicketCat()
    {
        try {
            $ticket_cat = ticket_category::with('ticket_sla')->orderBy('category_name', 'asc')->get();
            $ticket_sla = ticket_sla::all();
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
            return view('category.ticket-cat', compact('permissions','ticket_cat','ticket_sla','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load category. ' . $e->getMessage());
        }
    }
    public function createCat(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'sla_type_id' => 'required|exists:ticket_sla,id', // Assuming 'ticket_slas' is the table for SLA
                'category_name' => 'required|string|max:255',
                'category_detail' => 'required|string|max:255',
                'category_type' => 'required|string|max:255',
            ]);
    
            ticket_category::create([
                'sla_type_id' => $validatedData['sla_type_id'],
                'category_name' => $validatedData['category_name'],
                'category_detail' => $validatedData['category_detail'],
                'category_type' => $validatedData['category_type'],
            ]);
            return redirect()->back()->with('success', 'Ticket category created successfully!');
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load cat. ' . $e->getMessage());
    }
}
public function DeleteTicketCat($id)
{
    $ticket_cat = ticket_category::findOrFail($id);
    $ticket_cat ->delete();
    return redirect()->route('category.ticket-cat')->with('success', 'Ticket Category deleted successfully.');
}
public function editTicketCat($id)
{
    try {
        $ticket_cat = ticket_category::findOrFail($id);
        $user = auth()->user();
        $ticket_sla = ticket_sla::all();
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
        return view('category.editTicketCat', compact('ticket_sla','ticket_cat','permissions','inboxTickets'));
    } catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to edit ticket category. ' . $e->getMessage());
    }
}
public function updateTicketCat(Request $request, $id)
{
    try {
        $validatedData = $request->validate([
            'sla_type_id' => 'required|exists:ticket_sla,id', // Check for valid SLA
            'category_name' => 'required|string|max:255',
            'category_detail' => 'required|string|max:255',
            'category_type' => 'required|string|max:255',
        ]);
        $ticket_cat = ticket_category::findOrFail($id);
        $ticket_cat->update([
            'sla_type_id' => $validatedData['sla_type_id'],
            'category_name' => $validatedData['category_name'],
            'category_detail' => $validatedData['category_detail'],
            'category_name' => $validatedData['category_name'],
            'category_type' => $validatedData['category_type'],
        ]);
        return redirect()->route('category.ticket-cat')->with('success', 'Ticket category updated successfully!');
    }
     catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to update ticket Category ' . $e->getMessage());
    }
}
// sms
public function viewTicketsms()
    {
        try {
            $messages = messages::get();
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
            return view('category.ticket-sms', compact('permissions','messages','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Messages. ' . $e->getMessage());
        }
    }
    public function createsms(Request $request)
    {
        try{
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:500',
           
        ]);
        messages::create([
            'name' => $validatedData['name'],
            'message' => $validatedData['message'],
        ]);
        return redirect()->back()->with('success', 'Message created successfully!');
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Messages. ' . $e->getMessage());
    }
}
    public function Deletesms($id)
    {
        $sms = messages::findOrFail($id);
        $sms ->delete();
        return redirect()->route('category.ticket-sms')->with('success', 'Ticket Sla deleted successfully.');
    }
    public function editsms($id)
    {
        try {
            $message = messages::findOrFail($id);
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
            return view('category.editsms', compact('message','permissions','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to edit message. ' . $e->getMessage());
        }
    }
    public function updatesms(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'message' => 'required|string|max:500',
               
            ]);
    
            $message = messages::findOrFail($id);
            $message->update([
                'name' => $validatedData['name'],
                'message' => $validatedData['message'],
                
            ]);
   
            return redirect()->route('category.ticket-sms')->with('success', 'Messsage updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to update Message. ' . $e->getMessage());
        }
    }
    public function viewMessages(Request $request) // Add $request parameter
    {
        try {
            $user = auth()->user();
            $permissions = $user->user_group->permissions;
            $userId = auth()->id();
    
            // Fetch messages with their relationships (e.g., with 'messages' model)
            $messagesQuery = cust_messages::with('messages');
    
            // Get start_date and end_date from request or set defaults
            $startDate = $request->input('start_date', now()->subWeek()->toDateString());  // Defaults to 1 week ago
            $endDate = $request->input('end_date', now()->toDateString());  // Defaults to current date
    
            // Apply date filter if custom dates are provided
            $messagesQuery->whereDate('created_at', '>=', $startDate)
                          ->whereDate('created_at', '<=', $endDate);
    
            // Filter by MSISDN if provided
            if ($request->filled('msisdn')) {
                $messagesQuery->where('msisdn', $request->msisdn);
            }
    
            // Get the filtered messages
            $messages = $messagesQuery->get();
            $messageCount = $messagesQuery->count();
            // Fetch SMS messages (smss) if needed, you can apply similar filters here if required
            $smss = messages::get(); // Assuming no need to filter, but can be filtered similarly if required
    
            // Fetch Inbox Tickets (with additional filters)
            $inboxTickets = ticket_resolutions::selectRaw('
                    MAX(id) as id, 
                    ticket_id, 
                    MAX(created_at) as created_at, 
                    MAX(opened) as opened'
                )
                ->whereHas('tickets', function ($query) use ($userId) {
                    // Ensure the ticket is associated with the authenticated user
                    $query->where('user_id', $userId)
                          ->where('closed', 'no')
                          ->where('opened', 'no');
                })
                ->groupBy('ticket_id')
                ->orderByRaw('MAX(created_at) desc')
                ->with(['tickets' => function ($query) {
                    $query->with(['ticket_category', 'user', 'claimer', 'ticket_category.ticket_sla']);
                }])
                ->count();
    
            // Return the view with filtered data
            return view('category.Messages', compact('permissions', 'messages', 'inboxTickets', 'smss','messageCount'));
        } catch (\Exception $e) {
            // If an error occurs, redirect back with an error message
            return redirect()->back()->withErrors('Failed to load Messages. ' . $e->getMessage());
        }
    }
    
    public function createMessages(Request $request)
    {
        try {
            // Validate incoming request
            $validatedData = $request->validate([
                'msisdn' => 'required|string',    // Comma-separated MSISDNs
                'message_id' => 'required|string|max:500', // Message content
            ]);
    
            // Get the input MSISDNs and split them into an array
            $msisdns = explode(',', $request->input('msisdn'));
            $apiUrl = env('SMS_API_URL', 'https://172.28.14.2:9800/api/SubmitSMS/');
            $apiKey = env('SMS_API_KEY', 'eX3Q2vmmfgQNvCpJzbLeeNn9jBUn/ysfoHCxsEoR/g0=');
            $uid = env('SMS_API_UID', '100');
            $messageContent = $request->input('message_id');
            $messageCustomer = $request->input('message');
    
            foreach ($msisdns as $msisdn) {
                // Trim each MSISDN to remove spaces
                $trimmedMsisdn = trim($msisdn);
                
                // Save the message to the database
                $message = new cust_messages();
                $message->msisdn = $trimmedMsisdn;
                $message->message_id = $messageContent;
                $message->message = $messageCustomer;
                $message->save();
    
                // Prepare the payload for the SMS API
                $payload = [
                    "UId" => $uid,
                    "ApiKey" => $apiKey,
                    "Recipient" => $trimmedMsisdn,
                    "Message" => $messageCustomer,
                ];
    
                // Send the SMS using the API
                $response = Http::withoutVerifying()->post($apiUrl, $payload);
    

                // Optional: Check response status
                if (!$response->successful()) {
                    return redirect()->back()->withErrors("Failed to send SMS to $trimmedMsisdn.");
                }
            }
    
            return redirect()->back()->with('success', 'Messages sent successfully to all recipients!');
        } catch (\Exception $e) {
            // Handle exceptions
            return redirect()->back()->withErrors('Failed to process messages. Error: ' . $e->getMessage());
        }
    }
}
