<?php

namespace App\Http\Controllers;

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
use App\Models\products;
use Carbon\Carbon;
use App\Models\sales;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
class ReportsController extends Controller
{
    public function ServiceRecords(Request $request)
    {
        ini_set('memory_limit', '10240M');
        try {
            
$resolvedTicketsCounts = [];
$escalatedTicketsCounts = [];
$openOrInProgressOrEscalatedCounts = [];
$closedEscalatedCounts = [];
$ticketData = []; // Array to store data for Blade

// Filtering for 'call_center' work location
$datesCallCenter = tickets::selectRaw('DATE(created_at) as Date_record')
    ->whereIn('user_id', function ($query) {
        $query->select('id')
            ->from('users')
            ->where('location', 'call_center');
    })
    ->groupBy('Date_record')
    ->get();

foreach ($datesCallCenter as $date) {
    // Get tickets for the specific date and 'call_center' work location
    $ticketsForDate = tickets::whereDate('created_at', $date->Date_record)
        ->whereIn('user_id', function ($query) {
            $query->select('id')
                ->from('users')
                ->where('location', 'call_center');
        })
        ->get();

    $totalTickets = $ticketsForDate->count();
    $resolvedTickets = $ticketsForDate->where('interaction_status', 'Resolved')->count();
    $escalatedTickets = $ticketsForDate->where('interaction_status', 'Escalated')->count();

    // Count open, in progress, or escalated tickets
    $openOrInProgressOrEscalated = tickets::whereDate('created_at', $date->Date_record)
        ->whereIn('user_id', function ($query) {
            $query->select('id')
                ->from('users')
                ->where('location', 'call_center');
        })
        ->whereIn('ticket_status', ['open', 'inprogress', 'escalated'])
        ->count();

    // Count escalated tickets that are closed
    $closedEscalated = tickets::whereDate('created_at', $date->Date_record)
        ->whereIn('user_id', function ($query) {
            $query->select('id')
                ->from('users')
                ->where('location', 'call_center');
        })
        ->where('interaction_status', 'Escalated')
        ->where('ticket_status', 'closed')
        ->count();

    // Count within time and out of time tickets
    $withinTimeTickets = tickets::whereDate('created_at', $date->Date_record)
        ->whereIn('user_id', function ($query) {
            $query->select('id')
                ->from('users')
                ->where('location', 'call_center');
        })
        ->where('interaction_status', 'Escalated')
        ->where(function ($query) {
            $query->whereNull('ticket_age')
                ->orWhere('ticket_age', 'Within Time');
        })
        ->count();

    $outOfTimeTickets = tickets::whereDate('created_at', $date->Date_record)
        ->whereIn('user_id', function ($query) {
            $query->select('id')
                ->from('users')
                ->where('location', 'call_center');
        })
        ->where('interaction_status', 'Escalated')
        ->where('ticket_age', 'Out of Time')
        ->count();

    // Calculate percentages
    $perFCR = $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 2) : 0.00;
    $perWithinTime = $escalatedTickets > 0 ? round(($withinTimeTickets / $escalatedTickets) * 100, 2) : 0.00;
    $perOutOfTime = $escalatedTickets > 0 ? round(($outOfTimeTickets / $escalatedTickets) * 100, 2) : 0.00;

    // Store the results for Blade
    $ticketData[$date->Date_record] = [
        'total_tickets' => $totalTickets,
        'resolved_tickets' => $resolvedTickets,
        'escalated_tickets' => $escalatedTickets,
        'open_or_inprogress_or_escalated' => $openOrInProgressOrEscalated,
        'closed_escalated' => $closedEscalated,
        'per_fcr' => $perFCR,
        'per_withintime' => $perWithinTime,
        'per_outoftime' => $perOutOfTime,
    ];
}

$workLocationFilter = $request->input('work_location');
$startDate = $request->input('start_date');
$endDate = $request->input('end_date');

// Query base tickets for 'call_center' or 'stores', or show all if no filter is applied
$query = tickets::query();

// Apply the work location filter if set
if ($workLocationFilter) {
    $query->whereIn('user_id', function ($subquery) use ($workLocationFilter) {
        $subquery->select('id')
            ->from('users')
            ->where('location', 'stores')
            ->where('work_location', $workLocationFilter);
    });
}

// Apply date filter if set
if ($startDate && $endDate) {
    $query->whereBetween('created_at', [$startDate, $endDate]);
}

// Group by the date (without aliasing)
$dates = $query->selectRaw('DATE(created_at) as created_date')
    ->groupBy('created_date')
    ->get();

// Initialize the ticket data for Blade
$ticketStores = [];

foreach ($dates as $date) {
    // Fetch tickets for the specific date
    $ticketsForDate = tickets::whereDate('created_at', $date->created_date)
    ->whereIn('user_id', function ($query) {
        $query->select('id')
            ->from('users')
            ->where('location', 'stores');
    })->get();

    

    // Use query to count resolved and escalated tickets directly
    $resolvedStoresTickets = tickets::whereDate('created_at', $date->created_date)
    ->where('interaction_status', 'Resolved')
    ->whereIn('user_id', function ($query) {
        $query->select('id')
            ->from('users')
            ->where('location', 'stores');
    })
    ->count();

// Use query to count escalated tickets for users in 'stores'
$escalatedStoresTickets = tickets::whereDate('created_at', $date->created_date)
    ->where('interaction_status', 'Escalated')
    ->whereIn('user_id', function ($query) {
        $query->select('id')
            ->from('users')
            ->where('location', 'stores');
    })
    ->count();

// Use query to count open, in progress, or escalated tickets for users in 'stores'
$openOrInProgressOrEscalatedStores = tickets::whereDate('created_at', $date->created_date)
    ->whereIn('ticket_status', ['open', 'inprogress', 'escalated'])
    ->whereIn('user_id', function ($query) {
        $query->select('id')
            ->from('users')
            ->where('location', 'stores');
    })
    ->count();

// Use query to count closed escalated tickets for users in 'stores'
$closedEscalatedStores = tickets::whereDate('created_at', $date->created_date)
    ->where('interaction_status', 'Escalated')
    ->where('ticket_status', 'closed')
    ->whereIn('user_id', function ($query) {
        $query->select('id')
            ->from('users')
            ->where('location', 'stores');
    })
    ->count();

// Use query to count 'within time' escalated tickets for users in 'stores'
$withinTimeStoresTickets = tickets::whereDate('created_at', $date->created_date)
    ->where('interaction_status', 'Escalated')
    ->where(function ($subquery) {
        $subquery->whereNull('ticket_age')
            ->orWhere('ticket_age', 'Within Time');
    })
    ->whereIn('user_id', function ($query) {
        $query->select('id')
            ->from('users')
            ->where('location', 'stores');
    })
    ->count();

// Use query to count 'out of time' escalated tickets for users in 'stores'
$outOfTimeStoresTickets = tickets::whereDate('created_at', $date->created_date)
    ->where('interaction_status', 'Escalated')
    ->where('ticket_age', 'Out of Time')
    ->whereIn('user_id', function ($query) {
        $query->select('id')
            ->from('users')
            ->where('location', 'stores');
    })
    ->count();

    // Calculate percentages
    $perFCRStores = $ticketsForDate->count() > 0 ? round(($resolvedStoresTickets / $ticketsForDate->count()) * 100, 2) : 0.00;
    $perWithinTimeStores = $escalatedStoresTickets > 0 ? round(($withinTimeStoresTickets / $escalatedStoresTickets) * 100, 2) : 0.00;
    $perOutOfTimeStores = $escalatedStoresTickets > 0 ? round(($outOfTimeStoresTickets / $escalatedStoresTickets) * 100, 2) : 0.00;

    // Store the results for Blade
    $ticketStores[$date->created_date] = [
        'total_tickets' => $ticketsForDate->count(),
        'resolved_tickets' => $resolvedStoresTickets,
        'escalated_tickets' => $escalatedStoresTickets,
        'open_or_inprogress_or_escalated' => $openOrInProgressOrEscalatedStores,
        'closed_escalated' => $closedEscalatedStores,
        'per_fcr' => $perFCRStores,
        'per_withintime' => $perWithinTimeStores,
        'per_outoftime' => $perOutOfTimeStores,
    ];
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
        $work = User::whereNotNull('work_location')
        ->selectRaw('work_location, COUNT(*) as user_count')  // Only select work_location and aggregated user count
        ->groupBy('work_location')
        ->get();
            return view('reports.service_records', compact('permissions','inboxTickets','ticketData','work','ticketStores'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load sla. ' . $e->getMessage());
        }
    }
    public function agentRecords(Request $request)
    {
        ini_set('memory_limit', '10240M');
        try {
            $query = tickets::query();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $startOfMonth;
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : $endOfMonth;
       
            // Query for tickets
            $query = tickets::with(['ticket_category', 'user', 'closedBy', 'ticket_category.ticket_sla'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereHas('user', function ($query) {
                $query->where('location', 'call_center')
                    ->whereHas('user_group', function ($subQuery) {
                        $subQuery->where('group_name', 'customer support'); // Adjust casing if needed
                    });
            });
              
                if ($request->filled('ticked_by')) {
                    $query->where('user_id', $request->ticked_by);
                }
            
                $filteredTickets = $query->get();
                $totalTicketsAllAgents = $filteredTickets->count();
                $totalResolvedAllAgents = $filteredTickets->where('interaction_status', 'Resolved')->count();
                $totalEscalatedAllAgents = $filteredTickets->where('interaction_status', 'Escalated')->count();
            
                $agentStats = $query->select('user_id')
                    ->selectRaw('COUNT(*) as total_tickets')
                    ->selectRaw("SUM(CASE WHEN interaction_status = 'resolved' THEN 1 ELSE 0 END) as total_resolved")
                    ->selectRaw("SUM(CASE WHEN interaction_status = 'escalated' THEN 1 ELSE 0 END) as total_escalated")
                    ->selectRaw("ROUND(COUNT(*) / {$totalTicketsAllAgents} * 100, 1) as percentage_total_tickets")
                    ->selectRaw("ROUND(SUM(CASE WHEN interaction_status = 'resolved' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as percentage_resolved")
                    ->selectRaw("ROUND(SUM(CASE WHEN interaction_status = 'escalated' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as percentage_escalated")
                    ->groupBy('user_id')
                    ->orderBy('total_tickets', 'desc') 
                    ->get();

                    $query2 = tickets::query();
                    $startOfMonth2 = Carbon::now()->startOfMonth();
                    $endOfMonth2 = Carbon::now()->endOfMonth();
                    $startDate2 = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $startOfMonth2;
                    $endDate2 = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : $endOfMonth2;
                 
                
                    $query2->whereHas('user', function ($query) use ($request) {
                        // Filter by 'call_center' location and 'customer support' group_name
                        $query->where('location', 'stores')
                            ->whereHas('user_group', function ($subQuery) {
                                $subQuery->where('group_name', 'service center'); // Adjust casing if needed
                            });
                   

                        if ($request->filled('work_location')) {
                            $query->where('work_location', $request->input('work_location'));
                        }
                    });
                    
                    // Filter by ticketed agent (user_id)
                    if ($request->filled('ticket_by')) {
                        $query2->where('user_id', $request->input('ticket_by'));
                    }
                    
                        $filteredTickets2 = $query2->get();
                        $totalTicketsAllAgents2 = $filteredTickets2->count();
                        $totalResolvedAllAgents2 = $filteredTickets2->where('interaction_status', 'Resolved')->count();
                        $totalEscalatedAllAgents2 = $filteredTickets2->where('interaction_status', 'Escalated')->count();
                    
                        $agentStats2 = $query2->select('user_id')
                            ->selectRaw('COUNT(*) as total_tickets')
                            ->selectRaw("SUM(CASE WHEN interaction_status = 'resolved' THEN 1 ELSE 0 END) as total_resolved")
                            ->selectRaw("SUM(CASE WHEN interaction_status = 'escalated' THEN 1 ELSE 0 END) as total_escalated")
                            ->selectRaw("ROUND(COUNT(*) / {$totalTicketsAllAgents2} * 100, 1) as percentage_total_tickets")
                            ->selectRaw("ROUND(SUM(CASE WHEN interaction_status = 'resolved' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as percentage_resolved")
                            ->selectRaw("ROUND(SUM(CASE WHEN interaction_status = 'escalated' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as percentage_escalated")
                            ->groupBy('user_id')
                            ->orderBy('total_tickets', 'desc') 
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
        $user_group = User::with('user_group')
                ->whereHas('user_group', function($query) {
                    $query->where('group_name', 'customer support'); // Adjust casing if necessary
                })
              ->where('location', 'call_center')  
                ->get(); 
                $group = User::with('user_group')
                ->whereHas('user_group', function($query) {
                    $query->where('group_name', 'service center'); // Adjust casing if necessary
                })
              ->where('location', 'stores')  
                ->get(); 
                $work = User::whereNotNull('work_location')
                ->selectRaw('work_location, COUNT(*) as user_count')  // Only select work_location and aggregated user count
                ->groupBy('work_location')
                ->get();
            return view('reports.agent-records', compact('permissions','work','user_group','group','inboxTickets','agentStats', 'totalTicketsAllAgents', 'totalResolvedAllAgents', 'totalEscalatedAllAgents','agentStats2', 'totalTicketsAllAgents2', 'totalResolvedAllAgents2', 'totalEscalatedAllAgents2'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Agents reports. ' . $e->getMessage());
        }
    }
    public function reports(Request $request)
    {
        ini_set('memory_limit', '10240M');
        try {
            
// Start of the current week (Monday by default)
$currentWeekStart = Carbon::now()->startOfWeek();
$currentWeekEnd = Carbon::now()->endOfWeek();

// Initialize the query builder with necessary relations
$query = tickets::with(['ticket_resolutions' => function ($query) {
    $query->where('closed', 'final');
}])->orderBy('created_at', 'desc');

// Check if date filters are applied
if ($request->has('start_date') && $request->has('end_date')) {
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'date_type' => 'required|in:created_at,closed_date' // Validate date type
    ]);

    // Get the selected date type (either 'created_at' or 'closed_date')
    $dateType = $request->input('date_type');

    // Apply the query filter using the selected date type and provided date range
    $query->whereDate("tickets.{$dateType}", '>=', $request->start_date)
          ->whereDate("tickets.{$dateType}", '<=', $request->end_date);
} else {
    // If no date filters are applied, default to filtering by the current week
    $query->whereBetween('created_at', [$currentWeekStart, $currentWeekEnd]);
}

// Additional filters
if ($request->filled('ticket_status')) {
    $query->where('ticket_status', $request->ticket_status);
}
if ($request->filled('interaction_status')) {
    $query->where('interaction_status', $request->interaction_status);
}
if ($request->filled('user_id')) {
    $query->where('user_id', $request->user_id);
}
if ($request->filled('closed_by')) {
    $query->where('closed_by', $request->closed_by);
}
if ($request->filled('cat_id')) {
    $query->where('cat_id', $request->cat_id);
}
if ($request->filled('cat_name')) {
    $ids = explode(',', $request->cat_name); // Convert comma-separated IDs to an array
    $query->whereIn('cat_id', $ids); // Filter using whereIn
}
if ($request->filled('escalation_group')) {
    $query->where('escalation_group', $request->escalation_group);
}
if ($request->filled('ticket_age')) {
    $query->where('ticket_age', $request->ticket_age);
}
// Apply location-based filters if set in the request
$query->whereHas('user', function ($query) use ($request) {
    if ($request->filled('work_location')) {
        $query->where('work_location', $request->input('work_location'));
    }
    if ($request->filled('location')) {
        $query->where('location', $request->input('location'));
    }
});

// Count the total tickets after applying all filters
$ticketCount = $query->count();

// Retrieve the tickets with pagination
$tickets = $query->get(); // Or adjust the pagination as needed

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
        $work = User::whereNotNull('work_location')
        ->selectRaw('work_location, COUNT(*) as user_count')  // Only select work_location and aggregated user count
        ->groupBy('work_location')
        ->get();
        
        $ticket_by = User::with('user_group')
                ->whereHas('user_group', function($query) {
                    $query->where('group_name', 'customer support'); // Adjust casing if necessary
                })
                ->get(); 
                $closed_by = User::with('user_group')
                ->whereHas('user_group', function($query) {
                    $query->where('group_name', 'back office'); // Adjust casing if necessary
                })
                ->get(); 
                $complaint_detail = ticket_category:: get();
                $escalation_group = user_group:: get();
                $complaint_name = ticket_category::select(
                    'category_name',
                    DB::raw('GROUP_CONCAT(id) as ids'),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('category_name')
                ->get();
                
            return view('reports.reports', compact('permissions','escalation_group','tickets','inboxTickets','work','ticket_by','closed_by','complaint_detail','ticketCount','complaint_name'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load sla. ' . $e->getMessage());
        }
    }
    public function SalesReports(Request $request)
    {
        ini_set('memory_limit', '10240M');
        try { 
            
            $query = sales::with('product', 'user');

        // Check if filters are applied
        if ($request->has('start_date') && $request->has('end_date')) {
            // Apply date filters
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            // Default to daily records (today's records)
            $today = Carbon::today();
            $query->whereDate('created_at', $today);
        }
    

        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('product_id') && $request->product_id != '') {
            $query->where('product_id', $request->product_id);
        }
    
        if ($request->has('work_location') && $request->work_location != '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('work_location', $request->work_location);
            });
        }
        $sales = $query->get();
        foreach ($sales as $sale) {
            $sale->amount_cashed_in = $sale->quantity * $sale->amount;
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
      ->where('location', 'stores')  
        ->get(); 
        $work = User::whereNotNull('work_location')
        ->selectRaw('work_location, COUNT(*) as user_count')  // Only select work_location and aggregated user count
        ->groupBy('work_location')
        ->get();
    $products = products::get();

            return view('reports.sales-reports', compact('permissions','products','sales','inboxTickets','group','work'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load sla. ' . $e->getMessage());
        }
    }
}
