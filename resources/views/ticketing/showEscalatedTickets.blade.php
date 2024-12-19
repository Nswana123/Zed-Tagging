
<!DOCTYPE html>
<html>
<head>
    <title>Customer Support| CRM</title>
    @include('dashboard.style')
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/dashboard/style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>


    <style>
    .attachment-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        
    }
    .attachment-item {
        flex: 1 0 100px; /* Flex grow, shrink, and basis */
        max-width: 100px;
        text-align: center;
    }
    .attachment-item img {
        width: 100px;
        height: 100px;
        object-fit: contain; /* Ensures the image fits the box */
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .attachment-item a {
        display: block;
        padding: 5px;
        text-decoration: none;
        color: #007bff;
    }
    .attachment-item img {
        width: 150px;
        height: 150px;
        object-fit: contain;
        transition: transform 0.5s ease-in-out; /* Smooth transition for zooming */
    }

    .attachment-item img:hover {
        transform: scale(5); /* Zoom in by 1.5 times when hovered */
    }
    .collapse {
    transition: height 300s ease;
}
        </style>
</head>
<body>
<x-app-layout>
 
@include('dashboard.sidebar')
<div class="home-section">
    
@include('dashboard.header')

<div class="home-content p-3">
@if ($errors->any())
    <div class="alert alert-warning">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif 
<div class="row">
    <div class="col">
        <div class="card bg-body shadow-sm">
            <div class="card-header p-3 text-white float-centre" style="background:#0A2558;">
            <div class="row">  
                <div class="col">
                Name: {{ $ticket->title }} {{ $ticket->fname }} {{ $ticket->lname }}
                </div>
                <div class="col">
                    MSISDN: {{ $ticket->msisdn}}
                </div>
                <div class="col">
                    Primary No: {{ $ticket->primary_no}}
                </div>
                <div class="col">
            <label>Ticket # {{ $ticket->case_id}}<label>
                    
                </div>
                <div class="col">
                <label>Prefered Contact Method: {{ $ticket->method_of_contact }}</label> 
                </div>
            </div>
            <div class="row mt-3">
                <div class="col">
                    <label>Ticket Opened At {{ $ticket->created_at }}</label>
                </div>
                <div class="col">
                <label>Ticketed By {{ $ticket->user->fname }} {{ $ticket->user->lname }}</label> 
                </div>
                <div class="col">
                <label>Priority                            @if($ticket->ticket_category && $ticket->ticket_category->ticket_sla)
        @if($ticket->ticket_category->ticket_sla->priority == 'severe')
            <span class="badge badge-danger">Severe</span>
        @elseif($ticket->ticket_category->ticket_sla->priority == 'high')
            <span class="badge badge-warning">High</span>
        @elseif($ticket->ticket_category->ticket_sla->priority == 'medium')
            <span class="badge badge-warning">Medium</span>
        @else
            <span class="badge badge-success">Low</span>
        @endif
    @else
        <span class="badge badge-secondary">N/A</span>
    @endif</label>  
                </div>
                <div class="col">
                <label>Ticket Progress {{ $ticket->ticket_status }}</label> 
                </div>
                <div class="col">
                <label>Contact: {{ $ticket->contact }}</label> 
                </div>
            </div>
          
            </div>
            <div class="card-body">
                               <form action="{{ route('ticketing.EscalatedTicket', $ticket->id) }}" method="POST">
    @csrf
    @method('PUT') 
                                <div class="col-6-ml">
                                <fieldset class="border p-4">
                                <div class="row">
                <div class="col p-3">
                <div class="ticket">
    <p>Time Remaining: {{ $ticket->time_remaining['hours'] }} hours {{ $ticket->time_remaining['minutes'] }} minutes</p>
    <div class="progress-bar-container">

        <div class="progress-bar bg-primary " style="width: {{ $ticket->time_remaining['percentage'] }}%;"></div>
    </div>
</div>
                <!-- Customer's Issue Details -->
<fieldset class="border p-4 mb-4">
    <legend class="w-auto">Customer's Issue Details</legend>

    <!-- Complaint Category -->
    <div class="row">
    <div class="col-md mb-3">
        <label for="complaintCategory">Complaint Category*</label>
        <input type="text" class="form-control bg-body shadow-sm" name="category_name" id="category_name" 
        value="{{ old('category_name') ?? $ticket->ticket_category->category_name }}" autocomplete="off" readonly>
       
    </div>
    <div class="col-md mb-3">
        <label for="complaintCategory">Bought Product</label>
        <input type="text" class="form-control bg-body shadow-sm" name="product_id" id="category_name" 
        value="{{ old('product') ?? optional($ticket->products)->product }}"  autocomplete="off" 
        readonly>
       
    </div>
    </div>
    <!-- Issue Type -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="issue_type">Issue Type*</label>
            <input type="text" class="form-control bg-body shadow-sm" name="category_type" id="category_type" 
            value="{{ old('category_type') ?? $ticket->ticket_category->category_type }}" autocomplete="off" readonly>
            @error('issue_type')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Complaint Detail -->
        <div class="col-md-6 mb-3">
            <label for="issueDetail">Complaint Detail</label>
            <input type="text" class="form-control bg-body shadow-sm" name="category_detail" id="category_detail" 
            value="{{ old('category_detail') ?? $ticket->ticket_category->category_detail }}" autocomplete="off" readonly>
            @error('category_detail')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Issue Description -->
    <div class="mb-3">
        <label for="issue_description">Issue Description</label>
        <textarea class="form-control bg-body shadow-sm" name="issue_description" rows="2" id="issue_description" readonly>{{ old('issue_description') ?? $ticket->issue_description }}</textarea>
        <small id="descriptionHelp" class="form-text text-muted"></small>
        @error('issue_description')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <!-- Duration of Experience & Interaction Status -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="duration_of_experience">Duration Of Experience</label>
            <input type="text" class="form-control bg-body shadow-sm" name="duration_of_experience" id="duration_of_experience" 
                value="{{ old('duration_of_experience') ?? $ticket->duration_of_experience }}" autocomplete="off" readonly>
            <small id="doeHelp" class="form-text text-muted"></small>
            @error('duration_of_experience')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="interactionStatus">Continuous or Intermittent</label>
            <select class="form-control bg-body shadow-sm" name="issue_status" id="interactionStatus" readonly>
                <option value="">Is the Issue Continuous or Intermittent?</option>
                <option value="Continuous" {{ (old('issue_status') ?? $ticket->issue_status) == 'Continuous' ? 'selected' : '' }}>Continuous</option>
                <option value="Intermittent" {{ (old('issue_status') ?? $ticket->issue_status) == 'Intermittent' ? 'selected' : '' }}>Intermittent</option>
            </select>
            @error('issue_status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Customer Device & Physical Address -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="customer_device">Customer's Device</label>
            <input type="text" class="form-control bg-body shadow-sm" name="device_id" 
            value="{{ optional($ticket->device)->brand ?? 'N/A' }} {{ optional($ticket->device)->model ?? '' }}" autocomplete="off" readonly>
            @error('device_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="physical_address">Physical Address</label>
            <textarea class="form-control bg-body shadow-sm" name="location_id" rows="1" readonly>{{ optional($ticket->customerLocation)->province ?? 'N/A'}}, {{ optional($ticket->customerLocation)->town ?? '' }}, {{ optional($ticket->customerLocation)->landmark ?? ''}}</textarea>
            @error('physical_address')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>


    <!-- Interaction Status & Root Cause -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Interaction Status</label>
            <select class="form-control bg-body shadow-sm" name="interaction_status" readonly>
                <option value="">Select An Interaction Status</option>
                <option value="Resolved" {{ (old('interaction_status') ?? $ticket->interaction_status) == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Escalated" {{ (old('interaction_status') ?? $ticket->interaction_status) == 'Escalated' ? 'selected' : '' }}>Escalated</option>
            </select>
            @error('interaction_status')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label>Root Cause</label>
            <select name="root_cause" class="form-control bg-body shadow-sm" Required readonly>
        <option>Select problem Route Cause</option>
        <option value="Agent Error" {{ old('root_cause', $ticket) == 'Agent Error' ? 'selected' : '' }}>Agent Error</option>
    <option value="Wrong Configuration" {{ old('root_cause', $ticket) == 'Wrong Configuration' ? 'selected' : '' }}>Wrong Configuration</option>
    <option value="Customer Error" {{ old('root_cause', $ticket) == 'Customer Error' ? 'selected' : '' }}>Customer Error</option>
    <option value="Device Error" {{ old('root_cause', $ticket) == 'Device Error' ? 'selected' : '' }}>Device Error</option>
    <option value="Incompatible Device" {{ old('root_cause', $ticket) == 'Incompatible Device' ? 'selected' : '' }}>Incompatible Device</option>
    <option value="No Coverage" {{ old('root_cause', $ticket) == 'No Coverage' ? 'selected' : '' }}>No Coverage</option>
    <option value="Poor Coverage" {{ old('root_cause', $ticket) == 'Poor Coverage' ? 'selected' : '' }}>Poor Coverage</option>
    <option value="No Coverage (Fault)" {{ old('root_cause', $ticket) == 'No Coverage (Fault)' ? 'selected' : '' }}>No Coverage (Fault)</option>
    <option value="System Error" {{ old('root_cause', $ticket) == 'System Error' ? 'selected' : '' }}>System Error</option>
    <option value="Product Misinformation" {{ old('root_cause', $ticket) == 'Product Misinformation' ? 'selected' : '' }}>Product Misinformation</option>
    <option value="Depleted Bundle" {{ old('root_cause', $ticket) == 'Depleted Bundle' ? 'selected' : '' }}>Depleted Bundle</option>
    <option value="Wrong Bundle" {{ old('root_cause', $ticket) == 'Wrong Bundle' ? 'selected' : '' }}>Wrong Bundle</option>
    <option value="Delayed Activation" {{ old('root_cause', $ticket) == 'Delayed Activation' ? 'selected' : '' }}>Delayed Activation</option>
    <option value="Network Congestion" {{ old('root_cause', $ticket) == 'Network Congestion' ? 'selected' : '' }}>Network Congestion</option>
    <option value="SIM Card Issues" {{ old('root_cause', $ticket) == 'SIM Card Issues' ? 'selected' : '' }}>SIM Card Issues</option>
    <option value="Fraudulent Activity" {{ old('root_cause', $ticket) == 'Fraudulent Activity' ? 'selected' : '' }}>Fraudulent Activity</option>
    <option value="Billing Discrepancy" {{ old('root_cause', $ticket) == 'Billing Discrepancy' ? 'selected' : '' }}>Billing Discrepancy</option>
    <option value="Service Interruption" {{ old('root_cause', $ticket) == 'Service Interruption' ? 'selected' : '' }}>Service Interruption</option>
    <option value="Technical Maintenance" {{ old('root_cause', $ticket) == 'Technical Maintenance' ? 'selected' : '' }}>Technical Maintenance</option>
    <option value="Subscription Error" {{ old('root_cause', $ticket) == 'Subscription Error' ? 'selected' : '' }}>Subscription Error</option>
    <option value="Overcharged" {{ old('root_cause', $ticket) == 'Overcharged' ? 'selected' : '' }}>Overcharged</option>
    <option value="Unauthorized Account Changes" {{ old('root_cause', $ticket) == 'Unauthorized Account Changes' ? 'selected' : '' }}>Unauthorized Account Changes</option>
    <option value="Porting Issues" {{ old('root_cause', $ticket) == 'Porting Issues' ? 'selected' : '' }}>Porting Issues</option>
    <option value="Roaming Issues" {{ old('root_cause', $ticket) == 'Roaming Issues' ? 'selected' : '' }}>Roaming Issues</option>
    <option value="Application Error" {{ old('root_cause', $ticket) == 'Application Error' ? 'selected' : '' }}>Application Error</option>
    <option value="Miscommunication" {{ old('root_cause', $ticket) == 'Miscommunication' ? 'selected' : '' }}>Miscommunication</option>
    <option value="Expired Subscription" {{ old('root_cause', $ticket) == 'Expired Subscription' ? 'selected' : '' }}>Expired Subscription</option>
    <option value="Blocked Account" {{ old('root_cause', $ticket) == 'Blocked Account' ? 'selected' : '' }}>Blocked Account</option>
    <option value="Unresponsive Customer Care" {{ old('root_cause', $ticket) == 'Unresponsive Customer Care' ? 'selected' : '' }}>Unresponsive Customer Care</option>
    <option value="Data Throttling" {{ old('root_cause', $ticket) == 'Data Throttling' ? 'selected' : '' }}>Data Throttling</option>
    <option value="Misleading Promotions" {{ old('root_cause', $ticket) == 'Misleading Promotions' ? 'selected' : '' }}>Misleading Promotions</option>
    <option value="Hardware Malfunction" {{ old('root_cause', $ticket) == 'Hardware Malfunction' ? 'selected' : '' }}>Hardware Malfunction</option>
    <option value="Signal Interference" {{ old('root_cause', $ticket) == 'Signal Interference' ? 'selected' : '' }}>Signal Interference</option>
    <option value="Third-Party Provider Issues" {{ old('root_cause', $ticket) == 'Third-Party Provider Issues' ? 'selected' : '' }}>Third-Party Provider Issues</option>
    <option value="Unnotified Plan Changes" {{ old('root_cause', $ticket) == 'Unnotified Plan Changes' ? 'selected' : '' }}>Unnotified Plan Changes</option>
    <option value="Unauthorized Access" {{ old('root_cause', $ticket) == 'Unauthorized Access' ? 'selected' : '' }}>Unauthorized Access</option>
    <option value="User Authentication Issues" {{ old('root_cause', $ticket) == 'User Authentication Issues' ? 'selected' : '' }}>User Authentication Issues</option>
    <option value="Expired SIM" {{ old('root_cause', $ticket) == 'Expired SIM' ? 'selected' : '' }}>Expired SIM</option>
    <option value="Compatibility Updates Needed" {{ old('root_cause', $ticket) == 'Compatibility Updates Needed' ? 'selected' : '' }}>Compatibility Updates Needed</option>
    <option value="Call Drop Issues" {{ old('root_cause', $ticket) == 'Call Drop Issues' ? 'selected' : '' }}>Call Drop Issues</option>
    <option value="Slow Internet Speed" {{ old('root_cause', $ticket) == 'Slow Internet Speed' ? 'selected' : '' }}>Slow Internet Speed</option>
    <option value="Data Usage Misreporting" {{ old('root_cause', $ticket) == 'Data Usage Misreporting' ? 'selected' : '' }}>Data Usage Misreporting</option>
    <option value="Unreceived OTP" {{ old('root_cause', $ticket) == 'Unreceived OTP' ? 'selected' : '' }}>Unreceived OTP</option>
    <option value="Duplicate Charges" {{ old('root_cause', $ticket) == 'Duplicate Charges' ? 'selected' : '' }}>Duplicate Charges</option>
    <option value="Voice Quality Issues" {{ old('root_cause', $ticket) == 'Voice Quality Issues' ? 'selected' : '' }}>Voice Quality Issues</option>
    <option value="Delayed Refunds" {{ old('root_cause', $ticket) == 'Delayed Refunds' ? 'selected' : '' }}>Delayed Refunds</option>
    <option value="Invalid Recharge" {{ old('root_cause', $ticket) == 'Invalid Recharge' ? 'selected' : '' }}>Invalid Recharge</option>
    <option value="Missed Plan Benefits" {{ old('root_cause', $ticket) == 'Missed Plan Benefits' ? 'selected' : '' }}>Missed Plan Benefits</option>
    <option value="Promotional Spam" {{ old('root_cause', $ticket) == 'Promotional Spam' ? 'selected' : '' }}>Promotional Spam</option>
    <option value="Unauthorized SIM Swap" {{ old('root_cause', $ticket) == 'Unauthorized SIM Swap' ? 'selected' : '' }}>Unauthorized SIM Swap</option>
    <option value="Unsupported Features" {{ old('root_cause', $ticket) == 'Unsupported Features' ? 'selected' : '' }}>Unsupported Features</option>
    <option value="Error in International Roaming" {{ old('root_cause', $ticket) == 'Error in International Roaming' ? 'selected' : '' }}>Error in International Roaming</option>
    <option value="Downtime Notification Delay" {{ old('root_cause', $ticket) == 'Downtime Notification Delay' ? 'selected' : '' }}>Downtime Notification Delay</option>
    </select>

            
            @error('root_cause')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Action Taken -->
    <div class="row">
        <div class="col">
       <!-- Action Taken -->
<div class="col">

    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col">
                <div class="row">
                        <div class="col">
                        <label >Ticket Quality</label>
                        <select class="form-control bg-body shadow-sm" name="ticket_quality">
                <option value="normal" {{old('ticket_quality', $ticket) == 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="good" {{old('ticket_quality', $ticket) == 'good' ? 'selected' : '' }}>Good</option>
                <option value="bad" {{old('ticket_quality', $ticket) == 'bad' ? 'selected' : '' }}>Bad</option>
            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col">
                        <label>Action Taken:</label>
                        <div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Checked APN Settings" value="Checked APN Settings"
    {{ is_array($ticket->action_taken) && in_array('Checked APN Settings', $ticket->action_taken) ? 'checked' : '' }} ">
    <label class="form-check-label" for="Checked APN Settings">Checked APN Settings</label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Checked the AgentView" value="Checked the AgentView"
    {{ is_array($ticket->action_taken) && in_array('Checked the AgentView', $ticket->action_taken) ? 'checked' : '' }} ">
    <label class="form-check-label" for="Checked the AgentView">Checked the AgentView</label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Restarted Device" value="Restarted Device"
    {{ is_array($ticket->action_taken) && in_array('Restarted Device', $ticket->action_taken) ? 'checked' : '' }} ">
    <label class="form-check-label" for="Restarted Device">Restarted Device</label>
</div>
    </div>
    <div class="col mt-2">
    <div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Put on Flight Mode" value="Put on Flight Mode"
    {{ is_array($ticket->action_taken) && in_array('Put on Flight Mode', $ticket->action_taken) ? 'checked' : '' }} ">
    <label class="form-check-label" for="Put on Flight Mode">Put on Flight Mode</label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Tried the sim in another device" value="Tried the sim in another device"
    {{ is_array($ticket->action_taken) && in_array('Tried the sim in another device', $ticket->action_taken) ? 'checked' : '' }} ">
    <label class="form-check-label" for="Tried the sim in another device">Tried the sim in another device</label>
</div>
</div>
                    </div>
                    <div class="row mt-3">
                        <div class="col">
                        <div class="row ">
                        <fieldset class="border p-4 mb-4">
    <legend class="w-auto">Attachments</legend>

    <div class="col mb-3">
        <!-- Display existing attachments -->
            @if($ticket->attachments->isNotEmpty())
                <label>Existing Attachments:</label>
                <div class="attachment-container mt-2" style="display: flex; flex-wrap: wrap; gap: 10px;">
    @foreach($ticket->attachments as $attachment)
        @if (Str::endsWith($attachment->file_path, ['jpg', 'jpeg', 'png', 'gif']))
            <!-- Display image preview if the attachment is an image -->
            <div class="attachment-item">
                <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="Attachment">
            </div>
        @else
            <!-- Display as a link if the attachment is not an image -->
            <div class="attachment-item">
                <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">{{ $attachment->file_name }}</a>
            </div>
        @endif
    @endforeach
</div>
@else
                <p>No attachments available.</p>
            @endif
                </div>
        
        </div>
    </div>
</div>    
     </div>    
</div>
</div>
<div class="col mb-3" >
     <div class="card" >
    <div class="card-header" id="resolutionHeader">
        <div class="row">
            <div class="col">
                <legend class="w-auto">Resolution Provided</legend>
            </div>
           
        </div>
    </div>
    
  
    <div class="card-body table-wrapper" style="height:350px; overflow-y:auto;">
        <div class="row">
            <!-- Loop through resolutions and separate them by the current user or others -->
         
            @foreach($resolutions as $resolution)
            @if((int) $resolution->user_id === (int) auth()->user()->id)
                    <!-- Resolutions by the current user (Right-aligned) -->
                    <div class="col-12 text-end mb-3">
                        <div class="alert alert-primary" role="alert">
                            <strong>You:</strong> {{ $resolution->resolution_remarks }}
                            <br>
                            <small>{{ $resolution->created_at->format('d-m-Y H:i') }}</small>
                        </div>
                    </div>
                @else
                    <!-- Resolutions by other users (Left-aligned) -->
                    <div class="col-12 text-start mb-3">
                        <div class="alert alert-secondary" role="alert">
                        <strong>{{ $resolution->user->fname ?? 'User Name' }} {{ $resolution->user->lname ?? '' }}:</strong> 
                        {{ $resolution->resolution_remarks }}
                        <br>
                            <small>{{ $resolution->created_at->format('d-m-Y H:i') }}</small>
                        </div>
                    </div>
                @endif
            @endforeach
           
        
        </div>
    </div>
         <!-- New Resolution Form (Fixed at the Bottom) -->
    <div class="card-footer">
            <div class="input-group">
                <textarea class="form-control" name="resolution_remarks" rows="2" placeholder="Enter new resolution..." required></textarea>
                <button class="btn btn-primary" name="action" value="save">Send</button>
            </div>
        
    </div>
        </div>
</div>

    </div>
</div>


        </div>
      

            </div>
        </div>
    
            <div class="row mt-3">
        <div class="col float-end">
        <div class="col float-end">
            
        <button type="submit" name="action" value="comment" class="btn btn-primary float-end m-3">
                <i class='bx bx-window-close'></i> Save Comment
            </button>
            <button type="submit" name="action" value="closed" class="btn btn-primary float-end m-3">
                <i class='bx bx-window-close'></i> Resolve Ticket </button>
           
        </div>
        
    </form>
            </div>
    </div>
</div>
</div>

                </div>
            </div>

          
        </div>
    </div>
    <div style="margin-bottom: 200px;"></div>
@include('dashboard.script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script> 
   $('#assignTicketModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var ticketId = button.data('ticket-id'); // Extract the ticket ID from the button's data attribute

    var modal = $(this);
    modal.find('.modal-body #ticket_id').val(ticketId); // Set the hidden input value
});
 function previewMultipleImages(event) {
        const previewsContainer = document.getElementById('previews');
        previewsContainer.innerHTML = ''; // Clear previous previews

        const files = event.target.files;

        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            const file = files[i];

            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('img-thumbnail');
                img.style.maxWidth = '100px';
                img.style.height = '100px';
                img.style.marginRight = '10px';
                img.style.marginTop = '10px';
                previewsContainer.appendChild(img);
            };

            // Read each selected file
            reader.readAsDataURL(file);
        }
    }
    </script>
    </x-app-layout>
</body>
</html>
