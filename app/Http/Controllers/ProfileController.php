<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
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
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
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
            return view('profile.edit', compact('permissions','inboxTickets'),[
                'user' => $request->user(),
            ]);
        }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();
        

        return Redirect::route('profile.edit')->with('success', 'profile updated Successfully');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
