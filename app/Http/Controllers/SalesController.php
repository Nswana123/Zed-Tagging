<?php

namespace App\Http\Controllers;
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
use App\Models\user_tickets;
use App\Models\assigned_tickets;
use App\Models\int_sales;
use App\Models\ticket_resolutions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class SalesController extends Controller
{
    public function viewProducts()
    {
        try {
            $products = products::get();
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
            return view('sales.products', compact('permissions','products','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load products. ' . $e->getMessage());
        }
    }
   
    public function createProducts(Request $request)
    {
        try{
        $validatedData = $request->validate([
            'product' => 'required|string|max:255',
        ]);
       products::create([
            'product' => $validatedData['product'],
        ]);
        return redirect()->back()->with('success', 'Product created successfully!');
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Products. ' . $e->getMessage());
    }
}
    public function DeleteProducts($id)
    {
        $products = products::findOrFail($id);
        $products ->delete();
        return redirect()->route('sales.products')->with('success', 'Product deleted successfully.');
    }
    // Sales 
    public function viewSales()
    {
        try {
            $products = products::get();
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
            return view('sales.Sales', compact('permissions','products','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Daily Sales. ' . $e->getMessage());
        }
    }
   
    public function createSales(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'msisdn' => 'required|numeric|digits:10',
                'primary_no' => 'nullable|numeric|digits:10',
                'nrc' => 'nullable|string|max:13',
                'title' => 'required|string|max:5',
                'fname' => 'required|string|max:50',
                'lname' => 'required|string|max:50',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|array',
                'quantity.*' => 'required|integer|min:1', // Quantity must be greater than 0
                'payment_type' => 'required|array',
                'payment_type.*' => 'required|string|in:cash,bank,airtel money,mtn money,zed wallet',
                'amount' => 'required|array',
                'amount.*' => 'required|numeric|min:0.01', // Ensure amount is greater than 0
                'volte_upsell' => 'required|string|in:yes,no',
                 'zedlife_upsell' => 'required|string|in:yes,no',
                 'notes' => 'nullable|string|max:500',
            ]);
    
            // Check if the validation fails
            if ($validator->fails()) {
                // Redirect back with input data and validation errors
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
    
            $intSale = new int_sales([
                'msisdn' => $request->msisdn,
                'primary_no' => $request->primary_no,
                'nrc' => $request->nrc,
                'title' => $request->title,
                'fname' => $request->fname,
                'lname' => $request->lname,
                'volte_upsell' => $request->volte_upsell,  // Add VoLTE upsell data
                'zedlife_upsell' => $request->zedlife_upsell, // Add Zedlife upsell data
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);
            
            $intSale->save(); // Save the int_sales record first to get an ID for reference
            
            // Loop through each product and create a sales record for each one
            foreach ($request->product_id as $index => $productId) {
                sales::create([
                    'msisdn' => $intSale->msisdn,
                    'primary_no' => $intSale->primary_no,
                    'nrc' => $intSale->nrc,
                    'title' => $intSale->title,
                    'fname' => $intSale->fname,
                    'lname' => $intSale->lname,
                    'product_id' => $productId, // Use the current product ID
                    'quantity' => $request->quantity[$index], // Use the quantity for the current product
                    'payment_type' => $request->payment_type[$index], // Use the payment type for the current product
                    'amount' => $request->amount[$index], // Use the amount for the current product
                    'volte_upsell' => $intSale->volte_upsell,  // Add VoLTE upsell data
                    'zedlife_upsell' => $intSale->zedlife_upsell, // Add Zedlife upsell data
                    'notes' => $intSale->notes,
                    'user_id' => $intSale->user_id, // Use the same user ID
                ]);
            }
            
            // Optionally, delete the original `int_sales` entry if not needed anymore
            $intSale->delete();
    
        return redirect()->back()->with('success', 'Sale created successfully!');
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to load Sales. ' . $e->getMessage());
    }
}
public function sales(Request $request)
{
    try {
        $msisdn = $request->input('msisdn');

        // Query the tickets table to find a ticket by MSISDN
        $sale = sales::where('msisdn', $msisdn)->first();
  
        // Check if the MSISDN exists
        if ($sale) {
            // Find all sales related to the MSISDN
            $sales = sales::where('msisdn', $msisdn)
                               ->orderBy('created_at', 'desc')
                               ->take(3)  // Fetch the latest 3 sales
                               ->get();

            // Return the found data
            return response()->json([
                'status' => 'found',
                'data' => [
                    'primary_no' => $sale->primary_no,
                    'nrc' => $sale->nrc,
                    'title' => $sale->title,
                    'fname' => $sale->fname,
                    'lname' => $sale->lname
                ],
                'sales' => $sales // Include tickets for the pop-up
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
public function viewSalesSammury(Request $request)
{
            try {
                $totalAmountCashedIn = 0; // Initialize total cashed in
        $query = sales::with('product', 'user');

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $today = Carbon::today();
            $query->whereDate('created_at', $today);
        }

        $product_id = $request->input('product_id');
        $user_id = $request->input('user_id');
        $work_location = $request->input('work_location');

        $salesQuery = sales::selectRaw('DATE(created_at) as sell_date, product_id, COUNT(*) as total_sales, SUM(quantity) as total_quantity, SUM(quantity * amount) as total_cashed_in')
            ->with('product')
            ->groupBy('sell_date', 'product_id');

        if ($request->has('start_date') && $request->has('end_date')) {
            $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $salesQuery->whereDate('created_at', $today);
        }

        if (!empty($product_id)) {
            $salesQuery->where('product_id', $product_id);
        }

        if (!empty($user_id)) {
            $salesQuery->where('user_id', $user_id);
        }

        if (!empty($work_location)) {
            $salesQuery->whereHas('user', function($query) use ($work_location) {
                $query->where('work_location', $work_location);
            });
        }

        $sales = $salesQuery->get();

        // Calculate the total amount cashed in for the whole result set
        foreach ($sales as $sale) {
            $totalAmountCashedIn += $sale->total_cashed_in;
        }

                // Fetch user data and permissions
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
        
                // Fetch user groups related to customer support located in stores
                $group = User::with('user_group')
                    ->whereHas('user_group', function($query) {
                        $query->where('group_name', 'service center');
                    })
                    ->where('location', 'stores')
                    ->get();
        
                // Count users by work location
                $work = User::whereNotNull('work_location')
                    ->selectRaw('work_location, COUNT(*) as user_count')
                    ->groupBy('work_location')
                    ->get();
        
                // Fetch all available products
                $products = products::all();
        
                // Return view with the collected data
                return view('sales.Sales-sammury', compact('permissions', 'products', 'sales','totalAmountCashedIn', 'inboxTickets', 'group', 'work'));
        
            } catch (\Exception $e) {
                // Redirect back with an error message in case of failure
                return redirect()->back()->withErrors('Failed to load Sale Summary Report. ' . $e->getMessage());
            }
        }
        public function salesAgentRecords(Request $request)
        {
            ini_set('memory_limit', '10240M');
            try{
                $totalAmountCashedIn = 0; // Initialize total cashed-in amount 
                $query = sales::with('product', 'user');
                
                // Check if filters are applied for date range
                if ($request->has('start_date') && $request->has('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $today = Carbon::today();
                    $query->whereDate('created_at', $today); // Default to today's date
                }
                
                // Filter by product_id, user_id, or work_location if provided
                $product_id = $request->input('product_id');
                $user_id = $request->input('user_id');
                $work_location = $request->input('work_location');
                
                // Prepare the query for the sales data
                $salesQuery = sales::select(
                        'user_id',
                        DB::raw('COUNT(*) as total_sales'), // Count total sales per user
                        DB::raw('SUM(quantity) as total_quantity'),
                        DB::raw('SUM(quantity * amount) as total_cashed_in')
                    )
                    ->with('user') // Include user relationship
                    ->groupBy('user_id');
                
                // Apply filters for date range, product_id, user_id, and work_location
                if ($request->has('start_date') && $request->has('end_date')) {
                    $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $salesQuery->whereDate('created_at', $today); // Default to today's date
                }
                
                if (!empty($product_id)) {
                    $salesQuery->where('product_id', $product_id);
                }
                
                if (!empty($user_id)) {
                    $salesQuery->where('user_id', $user_id);
                }
                
                if (!empty($work_location)) {
                    $salesQuery->whereHas('user', function ($query) use ($work_location) {
                        $query->where('work_location', $work_location);
                    });
                }
                
                // Fetch sales data
                $sales = $salesQuery->get();
                
                // Attach user's full name to each sale for display
                foreach ($sales as $sale) {
                    $sale->user_name = $sale->user ? $sale->user->fname . ' ' . $sale->user->lname : 'N/A';
                    $totalAmountCashedIn += $sale->total_cashed_in;
                }
                
                $user = auth()->user();
                $permissions = $user->user_group->permissions;
                $userId = auth()->id();
                
                // Get count of unread tickets for the logged-in user
                $inboxTickets = ticket_resolutions::selectRaw('
                    MAX(id) as id, 
                    ticket_id, 
                    MAX(created_at) as created_at, 
                    MAX(opened) as opened'
                )
                ->whereHas('tickets', function ($query) use ($userId) {
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
                
                // Fetch additional data for the view
                $products = products::all();
                $work = User::whereNotNull('work_location')
                    ->selectRaw('work_location, COUNT(*) as user_count')
                    ->groupBy('work_location')
                    ->get();
                
                $group = User::with('user_group')
                    ->whereHas('user_group', function ($query) {
                        $query->where('group_name', 'service center');
                    })
                    ->where('location', 'stores')
                    ->get();
                
                // Return the view with data
                return view('sales.Sales_agent-records', compact(
                    'sales',
                    'products',
                    'permissions',
                    'totalAmountCashedIn',
                    'work',
                    'inboxTickets',
                    'group'
                ));
            } catch (\Exception $e) {
                return redirect()->back()->withErrors('Failed to load Agents reports. ' . $e->getMessage());
            }
        }
        public function getSalesDetails(Request $request, $userId)
        {
            // Ensure you are using the request to get the date parameters
            $startDate = $request->has('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfDay();
            $endDate = $request->has('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();
        
            // Fetch sales data for the given user within the specified date range
            $salesDetails = Sales::with('product')
                ->where('user_id', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
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
                         ->whereHas('tickets', function ($query) use ($userId) {
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

    return view('sales.sales_details', compact('salesDetails', 'permissions','inboxTickets'));
}

    }

