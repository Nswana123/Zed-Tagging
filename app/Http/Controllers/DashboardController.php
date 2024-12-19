<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\products;
use App\Models\sales;
use App\Models\user_group;
use App\Models\permissions;
use App\Models\role_permissions;
use App\Models\User;
use App\Models\ticket_sla;
use App\Models\ticket_category;
use App\Models\tickets;
use App\Models\attachment;
use App\Models\noc_ticket_tbl;
use App\Models\fault_type_tbl;
use App\Models\user_tickets;
use App\Models\assigned_tickets;
use App\Models\ticket_resolutions;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;  
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
     public function getSidebarData()
    {
        $user = auth()->user();
        $today = Carbon::today();
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
        $totalTickets = tickets::whereDate('created_at', $today)->count();
        $closedTickets = tickets::where('ticket_status', 'closed')
        ->where('interaction_status','Escalated')
            ->whereDate('closed_date', $today)
            ->count();
        $FCRTickets = tickets::where('interaction_status', 'Resolved')
            ->whereDate('created_at', $today)
            ->count();
        $NonFCRTickets = tickets::where('interaction_status', 'Escalated')
            ->whereDate('created_at', $today)
            ->count();
        $openTickets = tickets::where('ticket_status', 'open')
        ->where('claim_status', 'open')->count();
        $inprogrssTickets = tickets::where('ticket_status', 'inprogress')->count();
        
        $escalatedTickets = tickets::where('escalation_status', 'open')->count();
        $claimedTickets = tickets::where('claim_status', 'claimed')
        ->where('ticket_status','inprogress')->count();
         $unclaimedTickets = tickets::where('claim_status', 'unclaimed')
        ->where('ticket_status', 'open')->count();
        $totalNocTickets = noc_ticket_tbl::whereDate('created_at', $today)->count();
        $closedNocTickets = noc_ticket_tbl::where('ticket_status', 'closed')
            ->whereDate('closed_date', $today)
            ->count();
        $escalatedNocTickets = noc_ticket_tbl::where('ticket_status','inprogress')
        ->where('escalation_status', 'open')->count();
        $openNocTickets = noc_ticket_tbl::where('ticket_status', 'open')->count();
        $inprogrssNocTickets = noc_ticket_tbl::where('ticket_status', 'inprogress')->count();

 $issueDetailsData = DB::table('tickets')
        ->join('ticket_category', 'tickets.cat_id', '=', 'ticket_category.id') // Join with the ticket_categories table
        ->select('category_detail', DB::raw('count(*) as count'))
        ->whereDate('tickets.created_at', today())  // Daily filter by default
        ->groupBy('category_detail')
        ->orderBy('count', 'desc')
        ->limit(10)
        ->get();
        $customerDevicesChart = DB::table('tickets')
        ->join('devices', 'tickets.device_id', '=', 'devices.id') // Join with the devices table
        ->join('ticket_category', 'tickets.cat_id', '=', 'ticket_category.id') // Join with the ticket_categories table
        ->select('devices.brand', 'devices.model', DB::raw('count(*) as count'))
        ->where('ticket_category.category_name', 'network')
        ->whereDate('tickets.created_at', today())
        ->groupBy('devices.brand', 'devices.model')
        ->orderBy('count', 'desc')
        ->limit(10)
        ->get();
$physicalAddressChart  = DB::table('tickets')
->join('customer_locations', 'tickets.location_id', '=', 'customer_locations.id') // Join with the devices table
->join('ticket_category', 'tickets.cat_id', '=', 'ticket_category.id') // Join with the ticket_category table
->select('customer_locations.province', 'customer_locations.town','customer_locations.landmark', DB::raw('count(*) as count'))
->where('ticket_category.category_name', 'network')
->whereDate('tickets.created_at', today()) // Daily filter by default
->groupBy('customer_locations.province', 'customer_locations.town','customer_locations.landmark')
->orderBy('count', 'desc')
->limit(10)
->get();
$routeCauseData = DB::table('tickets')
        ->select('root_cause', DB::raw('count(*) as count'))
        ->where('interaction_status','Escalated')
        ->whereDate('created_at', today())  // Daily filter by default
        ->groupBy('root_cause')
        ->orderBy('count', 'desc')
        ->limit(10)
        ->get();
$faultySitesData = DB::table('noc_ticket_tbl')
        ->select('site_name', DB::raw('count(*) as count'))
        ->whereDate('created_at', today())  // Daily filter by default
        ->groupBy('site_name')
        ->orderBy('count', 'desc')
        ->limit(10)
        ->get();
        $faultTypeData = DB::table('noc_ticket_tbl')
        ->join('fault_type_tbl', 'noc_ticket_tbl.sla_id', '=', 'fault_type_tbl.id') // Join with the ticket_categories table
        ->select('fault_type', DB::raw('count(*) as count'))
        ->whereDate('noc_ticket_tbl.created_at', today())  // Daily filter by default
        ->groupBy('fault_type')
        ->orderBy('count', 'desc')
        ->limit(10)
        ->get();
        return view('dashboard', compact('faultTypeData','faultySitesData','routeCauseData','physicalAddressChart','customerDevicesChart','issueDetailsData','permissions','inboxTickets','FCRTickets', 'NonFCRTickets', 'claimedTickets', 'inprogrssTickets', 'totalTickets', 'openTickets', 'closedTickets', 'escalatedTickets','unclaimedTickets', 'inprogrssNocTickets', 'totalNocTickets', 'openNocTickets', 'closedNocTickets', 'escalatedNocTickets'));
    }

    public function fetchData($filter)
    {
        $query = DB::table('tickets')
        ->join('ticket_category', 'tickets.cat_id', '=', 'ticket_category.id')
        ->select('category_detail', DB::raw('count(*) as count'))
        ->groupBy('category_detail')
        ->orderBy('count', 'desc')
        ->limit(10);
        switch ($filter) {
            case 'day':
                $query->whereDate('tickets.created_at', today());
                break;
            case 'week':
                $query->whereBetween('tickets.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('tickets.created_at', now()->month);
                break;
            case 'quarter':
                $currentQuarter = ceil(now()->month / 3);
                $query->whereRaw('QUARTER(tickets.created_at) = ?', [$currentQuarter]);
                break;
            case 'year':
                $query->whereYear('tickets.created_at', now()->year);
                break;
            default:
                $query->whereDate('tickets.created_at', today());
                break;
        }

        // Get the filtered data
        $issueDetailsData = $query->get();

        // Return JSON response for AJAX
        return response()->json($issueDetailsData);
    } 
    public function fetchDeviceData($filter)
    {
        $query = DB::table('tickets')
        ->join('devices', 'tickets.device_id', '=', 'devices.id') // Join with the devices table
        ->join('ticket_category', 'tickets.cat_id', '=', 'ticket_category.id') // Join with the ticket_categories table
        ->select('devices.brand', 'devices.model', DB::raw('count(*) as count'))
        ->where('ticket_category.category_name', 'network')
        ->groupBy('devices.brand', 'devices.model')
        ->orderBy('count', 'desc')
        ->limit(10);
        switch ($filter) {
            case 'day':
                $query->whereDate('tickets.created_at', today());
                break;
            case 'week':
                $query->whereBetween('tickets.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('tickets.created_at', now()->month);
                break;
            case 'quarter':
                $currentQuarter = ceil(now()->month / 3);
                $query->whereRaw('QUARTER(tickets.created_at) = ?', [$currentQuarter]);
                break;
            case 'year':
                $query->whereYear('tickets.created_at', now()->year);
                break;
            default:
                $query->whereDate('tickets.created_at', today());
                break;
        }
        $customerDevicesChart = $query->get();
        return response()->json($customerDevicesChart);
    }
    public function fetchLocationData($filter)
{
    $query = DB::table('tickets')
    ->join('customer_locations', 'tickets.location_id', '=', 'customer_locations.id') // Join with the devices table
->join('ticket_category', 'tickets.cat_id', '=', 'ticket_category.id') // Join with the ticket_category table
->select('customer_locations.province', 'customer_locations.town','customer_locations.landmark', DB::raw('count(*) as count'))
->where('ticket_category.category_name', 'network')
->groupBy('customer_locations.province', 'customer_locations.town','customer_locations.landmark')
->orderBy('count', 'desc')
->limit(10);

    switch ($filter) {
        case 'day':
            $query->whereDate('tickets.created_at', today());
            break;
        case 'week':
            $query->whereBetween('tickets.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereMonth('tickets.created_at', now()->month);
            break;
        case 'quarter':
            $currentQuarter = ceil(now()->month / 3);
            $query->whereRaw('QUARTER(tickets.created_at) = ?', [$currentQuarter]);
            break;
        case 'year':
            $query->whereYear('tickets.created_at', now()->year);
            break;
        default:
            $query->whereDate('tickets.created_at', today());
            break;
    }

    $physicalAddressChart = $query->get();
    return response()->json($physicalAddressChart);
}
public function fetchRouteCauseData($filter)
{
    $query = DB::table('tickets')
        ->select('root_cause', DB::raw('count(*) as count'))
        ->where('interaction_status','Escalated')
        ->groupBy('root_cause')
        ->orderBy('count', 'desc')
        ->limit(10);

    switch ($filter) {
        case 'day':
            $query->whereDate('created_at', today());
            break;
        case 'week':
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereMonth('created_at', now()->month);
            break;
        case 'quarter':
            $currentQuarter = ceil(now()->month / 3);
            $query->whereRaw('QUARTER(created_at) = ?', [$currentQuarter]);
            break;
        case 'year':
            $query->whereYear('created_at', now()->year);
            break;
        default:
            break;
    }
    $routeCauseData = $query->get();
    return response()->json($routeCauseData);
}
public function fetchFaultsitesData($filter)
{
    $query = DB::table('noc_ticket_tbl')
        ->select('site_name', DB::raw('count(*) as count'))
        ->groupBy('site_name')
        ->orderBy('count', 'desc')
        ->limit(10);

    switch ($filter) {
        case 'day':
            $query->whereDate('created_at', today());
            break;
        case 'week':
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereMonth('created_at', now()->month);
            break;
        case 'quarter':
            $currentQuarter = ceil(now()->month / 3);
            $query->whereRaw('QUARTER(created_at) = ?', [$currentQuarter]);
            break;
        case 'year':
            $query->whereYear('created_at', now()->year);
            break;
        default:
            break;
    }
    $faultySitesData = $query->get();
    return response()->json($faultySitesData);
}

public function fetchFaultytypeData($filter)
{
    $query = DB::table('noc_ticket_tbl')
    ->join('fault_type_tbl', 'noc_ticket_tbl.sla_id', '=', 'fault_type_tbl.id')
    ->select('fault_type', DB::raw('count(*) as count'))
    ->groupBy('fault_type')
    ->orderBy('count', 'desc')
    ->limit(10);
    switch ($filter) {
        case 'day':
            $query->whereDate('noc_ticket_tbl.created_at', today());
            break;
        case 'week':
            $query->whereBetween('noc_ticket_tbl.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'month':
            $query->whereMonth('noc_ticket_tbl.created_at', now()->month);
            break;
        case 'quarter':
            $currentQuarter = ceil(now()->month / 3);
            $query->whereRaw('QUARTER(noc_ticket_tbl.created_at) = ?', [$currentQuarter]);
            break;
        case 'year':
            $query->whereYear('noc_ticket_tbl.created_at', now()->year);
            break;
        default:
            $query->whereDate('noc_ticket_tbl.created_at', today());
            break;
    }

    // Get the filtered data
    $faultTypeData = $query->get();

    // Return JSON response for AJAX
    return response()->json($faultTypeData);
}
}
