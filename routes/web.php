<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Models\user_group;
use App\Models\permissions;
use App\Models\role_permissions;
use App\Models\User;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\NocTicketsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('/auth.login');
});
Route::get('/refresh-csrf', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});

Route::get('/check-session', function () {
    if (Auth::check()) {
        return response()->json(['status' => 'active']);
    }
    return response()->json(['status' => 'expired'], 401);
});
Route::get('/dashboard/chart-data/{filter}', [DashboardController::class, 'fetchData']);
Route::get('/dashboard/chart-devicedata/{filter}', [DashboardController::class, 'fetchDeviceData']);
Route::get('/dashboard/chart-locationdata/{filter}', [DashboardController::class, 'fetchlocationData']);
Route::get('/dashboard/chart-routecause/{filter}', [DashboardController::class, 'fetchRouteCauseData']);
Route::get('/dashboard/chart-faultysites/{filter}', [DashboardController::class, 'fetchFaultsitesData']);
Route::get('/dashboard/chart-faultytype/{filter}', [DashboardController::class, 'fetchFaultytypeData']);
Route::get('/dashboard', [DashboardController::class, 'getSidebarData'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
   
});
// System Settings
Route::middleware('auth')->group(function () {
    //user groups
    Route::get('/setting', [SettingsController::class, 'userGroup'])->name('settings.usergroup');
    Route::post('/settings.create-usergroup', [SettingsController::class, 'createUserGroups'])->name('create-usergroup');
    Route::delete('/user-group/{id}', [SettingsController::class, 'DeleteUserGroup'])->name('delete-usergroup');
    Route::get('/settings.user-group/{id}', [SettingsController::class, 'editUserGroup'])->name('settings.editUsergroup');
    Route::put('/user-group/{id}/update', [SettingsController::class, 'updateUserGroup'])->name('settings.update');
    //permissions
    Route::get('/settings', [SettingsController::class, 'userRole'])->name('settings.userRole');
    Route::post('/settings.create-userRole', [SettingsController::class, 'createUserRole'])->name('create-userRole');
    Route::delete('/user-role/{id}', [SettingsController::class, 'DeleteUserRole'])->name('delete-userrole');
    Route::get('/settings.user-role/{id}', [SettingsController::class, 'editUserRole'])->name('settings.editUserRole');
    Route::put('/user-role/{id}/update', [SettingsController::class, 'updateUserRole'])->name('settings.updateRole');
    // User Management

    Route::get('/settings.users', [SettingsController::class, 'user'])->name('settings.user');
    Route::post('/settings.create-user', [SettingsController::class, 'createUser'])->name('create-user');
    Route::get('/settings.user/{id}', [SettingsController::class, 'editUser'])->name('settings.editUser');
    Route::post('/users/{id}/update', [SettingsController::class, 'updateUser'])->name('setting.update');
    Route::patch('/settings/{id}/deactivate', [SettingsController::class, 'deactivate'])->name('deactivateUser');
    Route::patch('/settings/{id}/activate', [SettingsController::class, 'activate'])->name('activateUser');
    // System settings
    Route::get('/settings.system-settings', [SettingsController::class, 'systemSettings'])->name('settings.systemSettings');
    Route::post('/settings.create-settings', [SettingsController::class, 'createSettings'])->name('create-settings');
    Route::delete('/system-settings/{id}', [SettingsController::class, 'DeleteSettings'])->name('delete-settings');
   
    // 
});

// Ticket Sla & Category
Route::middleware('auth')->group(function () {
    // ticket sla
    Route::get('/category.ticket-sla', [CategoryController::class, 'viewTicketSla'])->name('category.ticket-sla');
   Route::post('/category.create-ticket-sla', [CategoryController::class, 'createSla'])->name('create-ticket-sla');
    Route::delete('/category-sla/{id}', [CategoryController::class, 'DeleteTicketSla'])->name('delete-TicketSla');
    Route::get('/category.edit-ticket-sla/{id}', [CategoryController::class, 'editTicketSla'])->name('category.editTicketSla');
    Route::post('/category-sla/{id}/update', [CategoryController::class, 'updateTicketSla'])->name('category.updateTicketSla');

    // ticket category
    Route::get('/category.ticket-cat', [CategoryController::class, 'viewTicketCat'])->name('category.ticket-cat');
    Route::post('/category.create-ticket-cat', [CategoryController::class, 'createCat'])->name('create-ticket-cat');
    Route::delete('/category-cat/{id}', [CategoryController::class, 'DeleteTicketCat'])->name('delete-TicketCat');
    Route::get('/category.edit-ticket-cat/{id}', [CategoryController::class, 'editTicketCat'])->name('category.editTicketCat');
    Route::post('/category-cat/{id}/update', [CategoryController::class, 'updateTicketCat'])->name('category.updateTicketCat');
    // Messages
    Route::get('/category.ticket-sms', [CategoryController::class, 'viewTicketsms'])->name('category.ticket-sms');
    Route::post('/category.create-sms', [CategoryController::class, 'createsms'])->name('create-sms');
    Route::delete('/category-sms/{id}', [CategoryController::class, 'Deletesms'])->name('delete-sms');
    Route::get('/category.edit-sms/{id}', [CategoryController::class, 'editsms'])->name('category.editsms');
    Route::post('/category-sms/{id}/update', [CategoryController::class, 'updatesms'])->name('category.updatesms');

    // Message to Customers
    Route::get('/category.messages', [CategoryController::class, 'viewMessages'])->name('Messages');
    Route::post('/category.createMessages', [CategoryController::class, 'createMessages'])->name('createMessages');
});

// Call Tagging and Tcketing
Route::middleware('auth')->group(function () {
    // Call Tagging
    Route::get('/ticketing.call-tagging', [TicketsController::class, 'viewCallTag'])->name('call-tagging');
    //Route::post('/category.create-ticket-cat', [CategoryController::class, 'createCat'])->name('create-ticket-cat');  

    //Ticketing
    Route::get('/ticketing.Ticketing', [TicketsController::class, 'viewTicketing'])->name('Ticketing');
    Route::post('/get-complaint-details', [TicketsController::class, 'getComplaintDetails'])->name('getComplaintDetails');

    Route::get('/tickets/search', [TicketsController::class, 'search'])->name('tickets.search');
    Route::get('/ticketing/customerProfile/{msisdn}', [TicketsController::class, 'customerProfile'])->name('ticketing.customerProfile');
    Route::post('/ticketing.create-ticket', [TicketsController::class, 'createTicket'])->name('create-ticket');
    Route::get('/check-msisdn', [TicketsController::class, 'msisdn'])->name('check.msisdn');
    // open Ticket
    Route::get('/ticketing.open-tickets', [TicketsController::class, 'viewOpenTicket'])->name('open-tickets');
    Route::get('/ticketing.edit-OpenTicket/{id}', [TicketsController::class, 'editOpenTicket'])->name('ticketing.showOpenTicket');
    Route::get('/ticketing/claim/{id}', [TicketsController::class, 'claimTicket'])->name('ticketing.claim');
    // claimed Tickets
    Route::get('/ticketing.claimedTickets', [TicketsController::class, 'ClaimedTickets'])->name('claimedTickets');
    Route::post('/ticketing.reassignTicket', [TicketsController::class, 'reAssignTicket'])->name('reassign-tickets');
    Route::get('/ticketing.showClaimedTcikets/{id}', [TicketsController::class, 'editClaimedTcikets'])->name('ticketing.showClaimedTcikets');
    Route::put('/claimedTicket/{id}', [TicketsController::class, 'updateClaimedtickets'])->name('ticketing.updateClaimedtickets');
    Route::get('/ticketing.inbox', [TicketsController::class, 'Inbox'])->name('inbox');
    Route::get('/ticketing.markOpened/{id}', [TicketsController::class, 'showInboxTickets'])->name('ticketing.showInboxTickets');
    Route::put('/ClosedInboxTicket/{id}', [TicketsController::class, 'updateInboxtickets'])->name('ticketing.updateInboxtickets');
    Route::post('/ticketing.escalationGroup', [TicketsController::class, 'escalationGroup'])->name('escalationGroup');
    //unclaimed
    Route::get('/ticketing.unclaimedTickets', [TicketsController::class, 'unClaimedTickets'])->name('unclaimedTickets');
    Route::post('/ticketing.assignTicket', [TicketsController::class, 'AssignTicket'])->name('assign-tickets');
     // cLOSED Ticket
     Route::get('/ticketing.Closed-tickets', [TicketsController::class, 'viewClosedTicket'])->name('Closed-tickets');
    Route::get('/ticketing.edit-closedTicket/{id}', [TicketsController::class, 'editClosedTicket'])->name('ticketing.showClosedTicket');
  // All Tickets Ticket
  Route::get('/ticketing.all-tickets', [TicketsController::class, 'viewallTicket'])->name('all-tickets');
 Route::get('/ticketing.edit-allTicket/{id}', [TicketsController::class, 'editallTicket'])->name('ticketing.showallTicket');

 Route::get('/ticketing.ticket-quality', [TicketsController::class, 'ticketQuality'])->name('ticket-quality');
 Route::get('/ticketing-ticket-quality/{id}', [TicketsController::class, 'showQuality'])->name('ticketing.showQuality');
 Route::put('/ticketing.quality/{id}', [TicketsController::class, 'updateQuality'])->name('ticketing.quality');

// refunds
Route::get('/ticketing.refundList', [TicketsController::class, 'allRefundList'])->name('refundList');
Route::get('/ticketing-ticket-ref/{id}', [TicketsController::class, 'showRefundList'])->name('ticketing.showRefundList');
Route::put('/refunds/{id}', [TicketsController::class, 'updateRefund'])->name('ticketing.updateRefund');
// Escalations
Route::get('/ticketing.escalated-Tickets', [TicketsController::class, 'EscalationTickets'])->name('EscalatedTickets');
Route::get('/ticketing.Editescalated-Tickets/{id}', [TicketsController::class, 'showEscalatedTickets'])->name('ticketing.showEscalatedTickets');
Route::put('/updateEscalatedTicket/{id}', [TicketsController::class, 'updateEscalatedTicket'])->name('ticketing.EscalatedTicket');

Route::get('/ticketing.markOpene/{id}', [TicketsController::class, 'showInboTickets'])->name('ticketing.showInboTickets');
    Route::put('/ClosedInboTicket/{id}', [TicketsController::class, 'updateInbotickets'])->name('ticketing.updateInbotickets');
// Resolved
Route::get('/ticketing.ResolvedTickets', [TicketsController::class, 'ResolvedTickets'])->name('ResolvedTickets');
Route::get('/ticketing.EditResolved-Tickets/{id}', [TicketsController::class, 'showResolvedTickets'])->name('ticketing.showResolvedTickets');
Route::put('/updateResolvedTicket/{id}', [TicketsController::class, 'updateResolvedTicket'])->name('ticketing.updateResolvedTicket');
// Reports
Route::get('/reports.service_records', [ReportsController::class, 'ServiceRecords'])->name('service_records');
Route::get('/reports.agent-records', [ReportsController::class, 'agentRecords'])->name('agent-records');
Route::get('/reports.sales-reports', [ReportsController::class, 'SalesReports'])->name('sales-reports');
Route::get('/sales.sales_agent-records', [SalesController::class, 'salesAgentRecords'])->name('Sales_agent-records');
Route::get('/sales/details/{userId}', [SalesController::class, 'getSalesDetails'])->name('sales.details');

Route::get('/reports.reports', [ReportsController::class, 'reports'])->name('reports');

// Products
Route::get('/sales.products', [SalesController::class, 'viewProducts'])->name('sales.products');
Route::post('/sales.create-products', [SalesController::class, 'createProducts'])->name('create-products');
Route::delete('/sales-products/{id}', [SalesController::class, 'DeleteProducts'])->name('delete-products');
// Sales
Route::get('/sales.Sales', [SalesController::class, 'viewSales'])->name('sales.Sales');
Route::post('/sales.create-Sales', [SalesController::class, 'createSales'])->name('create-Sales');
Route::get('/check-sales', [SalesController::class, 'sales'])->name('check.sales');

Route::get('/sales.Sales-sammury', [SalesController::class, 'viewSalesSammury'])->name('Sales-sammury');

// devices and Locations
Route::get('/devices.devices', [DevicesController::class, 'viewDevice'])->name('devices.devices');
Route::post('/devices.create-device', [DevicesController::class, 'createDevice'])->name('create-devices');
Route::delete('/devices-devices/{id}', [DevicesController::class, 'DeleteDevices'])->name('delete-devices');
Route::get('/devices.edit-Device/{id}', [DevicesController::class, 'editDevice'])->name('devices.editDevice');
Route::post('/devices-Device/{id}/update', [DevicesController::class, 'updateDevice'])->name('devices.updateDevice');

Route::get('/devices.location', [DevicesController::class, 'viewLocation'])->name('devices.location');
Route::post('/devices.create-location', [DevicesController::class, 'createLocation'])->name('create-location');
Route::delete('/devices-location/{id}', [DevicesController::class, 'DeleteLocation'])->name('delete-location');
Route::get('/devices.edit-Location/{id}', [DevicesController::class, 'editLocation'])->name('devices.editLocation');
Route::post('/Locations-Location/{id}/update', [DevicesController::class, 'updateLocation'])->name('Locations.updateLocation');

//NOC TICKETs
Route::get('/noc_tickets.tickets', [NocTicketsController::class, 'viewNocTickets'])->name('ticket');
Route::get('/noc_tickets/Sitesearch', [NocTicketsController::class, 'Sitesearch'])->name('noc_tickets.Sitesearch');
Route::get('/noc_tickets/SiteProfile/{site_name}', [NocTicketsController::class, 'SiteProfile'])->name('noc_tickets.SiteProfile');

Route::post('/noc_tickets.log-ticket', [NocTicketsController::class, 'LogTicket'])->name('log-ticket');
Route::get('/noc_tickets.NocOpenTickets', [NocTicketsController::class, 'viewOpenNocTicket'])->name('NocOpenTickets');
Route::get('/noc_tickets.showNocTickets/{id}', [NocTicketsController::class, 'showNocTickets'])->name('noc_tickets.showNocTickets');
Route::put('/UpdateNocTickets/{id}', [NocTicketsController::class, 'UpdateNocTicket'])->name('noc_tickets.UpdateNocTicket');
Route::post('/noc_tickets.escalateToFields', [NocTicketsController::class, 'escalateToFields'])->name('escalateToFields');
Route::post('/noc_tickets.assignNocTicket', [NocTicketsController::class, 'AssignNocTicket'])->name('assignTicket');

Route::get('/noc_tickets.NocAssignedTickets', [NocTicketsController::class, 'viewAssignedNocTicket'])->name('NocAssignedTickets');
Route::get('/noc_tickets.showAssignedTickets/{id}', [NocTicketsController::class, 'showAssignedTickets'])->name('noc_tickets.showAssignedTickets');
Route::put('/NocAssigned/{id}', [NocTicketsController::class, 'updateNocAssignedtickets'])->name('noc_tickets.updateNocAssignedtickets');
Route::put('/NocOpen/{id}', [NocTicketsController::class, 'updateNocOpentickets'])->name('noc_tickets.updateNocOpentickets');
Route::post('/noc_tickets.NocescalationGroup', [NocTicketsController::class, 'NocescalationGroup'])->name('NocescalationGroup');

// Add Faultsy
Route::get('/noc_tickets.sitefaults', [NocTicketsController::class, 'viewSiteFaults'])->name('noc_tickets.sitefaults');
Route::post('/noc_tickets.add-faulty', [NocTicketsController::class, 'addFaulty'])->name('add-faulty');
Route::delete('/noc_tickets-sla/{id}', [NocTicketsController::class, 'DeleteTFaults'])->name('delete-Faulty');
Route::get('/noc_tickets.edit-faulty/{id}', [NocTicketsController::class, 'editSiteFaulty'])->name('noc_tickets.editSiteFaulty');
Route::post('/noc_tickets-sla/{id}/update', [NocTicketsController::class, 'updateSiteFaulty'])->name('noc_tickets.updateSiteFaulty');

//All. Escalated And Closed
Route::get('/noc_tickets.NocAllTickets', [NocTicketsController::class, 'viewAllNocTicket'])->name('NocAllTickets');
Route::get('/noc_tickets.showAllTickets/{id}', [NocTicketsController::class, 'shoAllTickets'])->name('noc_tickets.showAllTickets');

Route::get('/noc_tickets.NocEscalatedTickets', [NocTicketsController::class, 'viewEscalatedNocTicket'])->name('NocEscalatedTickets');
Route::get('/noc-tickets/escalated/{id}', [NocTicketsController::class, 'showNocEscalated'])->name('noc_tickets.ShowNocEscalated');
Route::put('/UpdateNocescalated/{id}', [NocTicketsController::class, 'UpdateNocEscalated'])->name('noc_tickets.UpdateNocescalated');

Route::get('/noc_tickets.NocClosedTickets', [NocTicketsController::class, 'viewClosedNocTicket'])->name('NocClosedTickets');
Route::get('/noc_tickets.NocReport', [NocTicketsController::class, 'viewNocReport'])->name('NocReport');

});

require __DIR__.'/auth.php';
