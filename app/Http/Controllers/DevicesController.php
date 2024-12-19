<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\devices;
use App\Models\customer_locations;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class DevicesController extends Controller
{
    public function viewDevice()
    {
        try {
            $devices = devices::get();
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
            return view('devices.devices', compact('permissions','devices','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Devices ' . $e->getMessage());
        }
    }
   
    public function createDevice(Request $request)
    {
        try{
            $request->validate([
                'brand' => 'required|string|max:255',
                'model' => 'required|string|max:255',
            ]);
    
            // Create and store the device data in the database
            devices::create([
                'brand' => $request->brand,
                'model' => $request->model,
            ]);
           
    
        return redirect()->back()->with('success', 'Device created successfully!');
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to create device. ' . $e->getMessage());
    }
}
public function editDevice($id)
{
    $devices = devices::findOrFail($id);
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
    return view('devices.editDevice', compact('devices','permissions','inboxTickets'));
}
public function updateDevice(Request $request, $id)
{
    try {
        $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
        ]);
    
        $device = devices::findOrFail($id);
        $device->update([
            'brand' => $request->brand,
            'model' => $request->model,
        ]);
        return redirect()->route('devices.devices')->with('success', 'Device updated successfully!');
    }
     catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to update Device ' . $e->getMessage());
    }
}
public function DeleteDevices($id)
{
    $deivces = devices::findOrFail($id);
    $deivces ->delete();
    return redirect()->route('devices.devices')->with('success', 'Device deleted successfully.');
}
// locations 
public function viewLocation()
    {
        try {
            $locations = customer_locations::get();
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
            return view('devices.location', compact('permissions','locations','inboxTickets'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load Locations ' . $e->getMessage());
        }
    }
   
    public function createLocation(Request $request)
    {
        try{
            $request->validate([
                'province' => 'required|string|max:255',
                'town' => 'required|string|max:255',
                'landmark' => 'required|string|max:255',
            ]);
    
            // Create and store the device data in the database
            customer_locations::create([
                'province' => $request->province,
                'town' => $request->town,
                'landmark' => $request->landmark,
            ]);
    
        return redirect()->back()->with('success', 'Location created successfully!');
    }
    catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to create Location. ' . $e->getMessage());
    }
}
public function DeleteLocation($id)
{
    $location = customer_locations::findOrFail($id);
    $location ->delete();
    return redirect()->route('devices.location')->with('success', 'Location deleted successfully.');
}
public function editLocation($id)
{
    $locations = customer_locations::findOrFail($id);
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
    return view('devices.editLocation', compact('locations','permissions','inboxTickets'));
}
public function updateLocation(Request $request, $id)
{
    try {
        $request->validate([
            'province' => 'required|string|max:255',
            'town' => 'required|string|max:255',
            'landmark' => 'required|string|max:255',
        ]);
    
        $device = customer_locations::findOrFail($id);
        $device->update([
            'province' => $request->province,
            'town' => $request->town,
            'landmark' => $request->landmark,
        ]);
        return redirect()->route('devices.location')->with('success', 'Location updated successfully!');
    }
     catch (\Exception $e) {
        return redirect()->back()->withErrors('Failed to update Location ' . $e->getMessage());
    }
}
}
