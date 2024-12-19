
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
                <label>Ticket Opened At: {{ $ticket->created_at }}</label>
                </div> 
                <div class="col">
                Site Name: {{ $ticket->site_name }}
                </div>
                
                <div class="col">
                <label>Ticket Number {{ $ticket->case_id }}</label>
                </div>
            </div>
                <div class="row mt-3">
                <div class="col">
                <label>Ticketed By {{ $ticket->user->fname }} {{ $ticket->user->lname }}</label> 
                </div>
                <div class="col">
                <label>Priority                            @if($ticket->faulty_type)
        @if($ticket->faulty_type->priority == 'severe')
            <span class="badge badge-danger">Severe</span>
        @elseif($ticket->faulty_type->priority == 'high')
            <span class="badge badge-warning">High</span>
        @elseif($ticket->faulty_type->priority == 'medium')
            <span class="badge badge-warning">Medium</span>
        @else
            <span class="badge badge-success">Low</span>
        @endif
    @else
        <span class="badge badge-secondary">N/A</span>
    @endif</label>  
                </div>
                <div class="col">
                <label>Ticket Status: {{ $ticket->ticket_status }}</label> 
                </div>
            </div>
          
            </div>
            <div class="card-body">
                               <form action="{{ route('noc_tickets.updateNocAssignedtickets', $ticket->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT') 
    <fieldset class="border p-4 mb-4">
        <legend class="w-auto">Site Fault Details</legend>
        <div class="row">
                <div class="col p-3">
                <div class="ticket">
    <p>Time Remaining: {{ $ticket->time_remaining['hours'] }} hours {{ $ticket->time_remaining['minutes'] }} minutes</p>
    <div class="progress-bar-container">

        <div class="progress-bar bg-primary " style="width: {{ $ticket->time_remaining['percentage'] }}%;"></div>
    </div>
</div>
        <div class="row mt-3">
        <div class="col-lg">
            <label for="outage_duration">Fault Type</label>
            <input type="text" class="form-control bg-body shadow-sm" name="sla_id" id="fault_type" value="{{ old('faulty_type->fault_type ', $ticket->faulty_type->fault_type ?? '') }}" autocomplete="off">
            @error('sla-id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
       
        <div class="col-lg">
            <label>Fault Severity</label>
            <select name="fault_severity" class="form-control bg-body shadow-sm" id="severity">
                <option value="">Select Fault Severity</option>
                <option value="critical" {{ old('fault_severity', $ticket->fault_severity ?? '') == 'critical' ? 'selected' : '' }}>Critical - Immediate action required</option>
                <option value="high" {{ old('fault_severity', $ticket->fault_severity ?? '') == 'high' ? 'selected' : '' }}>High - Major impact on operations</option>
                <option value="medium" {{ old('fault_severity', $ticket->fault_severity ?? '') == 'medium' ? 'selected' : '' }}>Medium - Moderate impact on operations</option>
                <option value="low" {{ old('fault_severity', $ticket->fault_severity ?? '') == 'low' ? 'selected' : '' }}>Low - Minor impact on operations</option>
                <option value="informational" {{ old('fault_severity', $ticket->fault_severity ?? '') == 'informational' ? 'selected' : '' }}>Informational - No immediate action needed</option>
            </select>
            <small id="severityHelp" class="form-text text-muted"></small>
            @error('fault_severity')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
<div class="row">
    
</div>
    <div class="row mt-3">
        <div class="col-lg form-group">
            <label for="dateTime">Fault Occurrence Time</label>
            <input type="datetime-local" id="dateTime" name="fault_occurrence_time" class="form-control bg-body shadow-sm" value="{{ old('fault_occurrence_time', $ticket->fault_occurrence_time ?? '') }}">
            @error('fault_occurrence_time')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="col-lg">
            <label>Fault Root Cause</label>
            <select name="root_cause" class="form-control bg-body shadow-sm" id="root">
                <option value="">Select Fault Root Cause</option>
                <option value="power_outage" {{ old('root_cause', $ticket->root_cause ?? '') == 'power_outage' ? 'selected' : '' }}>Power Outage</option>
                <option value="equipment_failure" {{ old('root_cause', $ticket->root_cause ?? '') == 'equipment_failure' ? 'selected' : '' }}>Equipment Failure</option>
                <option value="fiber_cut" {{ old('root_cause', $ticket->root_cause ?? '') == 'fiber_cut' ? 'selected' : '' }}>Fiber Cut</option>
                <option value="weather_damage" {{ old('root_cause', $ticket->root_cause ?? '') == 'weather_damage' ? 'selected' : '' }}>Weather Damage</option>
                <option value="software_issue" {{ old('root_cause', $ticket->root_cause ?? '') == 'software_issue' ? 'selected' : '' }}>Software Issue</option>
                <option value="hardware_issue" {{ old('root_cause', $ticket->root_cause ?? '') == 'hardware_issue' ? 'selected' : '' }}>Hardware Issue</option>
                <option value="maintenance" {{ old('root_cause', $ticket->root_cause ?? '') == 'maintenance' ? 'selected' : '' }}>Scheduled Maintenance</option>
                <option value="unauthorized_access" {{ old('root_cause', $ticket->root_cause ?? '') == 'unauthorized_access' ? 'selected' : '' }}>Unauthorized Access/Vandalism</option>
                <option value="overcapacity" {{ old('root_cause', $ticket->root_cause ?? '') == 'overcapacity' ? 'selected' : '' }}>Network Overcapacity</option>
            </select>
            <small id="rootHelp" class="form-text text-muted"></small>
            @error('root_cause')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
    </div>

    <div class="row mt-3">
     
        <div class="col-lg">
            <label for="issue_description">Fault Description</label>
            <textarea class="form-control bg-body shadow-sm" name="fault_description" id="issue_description" rows="4">{{ old('fault_description', $ticket->fault_description ?? '') }}</textarea>
            <small id="descriptionHelp" class="form-text text-muted"></small>
            @error('fault_description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
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
                <input type="file" class="form-control bg-body shadow-sm" name="attachments[]" id="attachments" accept="image/*" multiple onchange="previewMultipleImages(event)">
                                            <div id="previews" class="mt-2" style="display: flex; flex-wrap: wrap;"></div> <!-- Container for image previews -->
                                            @error('attachments')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
            @endif
      
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
                            <small>{{ $resolution->created_at?->format('d-m-Y H:i') ?? 'N/A' }}</small>
                        </div>
                    </div>
                @else
                    <!-- Resolutions by other users (Left-aligned) -->
                    <div class="col-12 text-start mb-3">
                    <div class="alert alert-secondary" role="alert">
                        <strong>{{ $resolution->user->fname ?? 'User Name' }} {{ $resolution->user->lname ?? '' }}:</strong> 
                        {{ $resolution->resolution_remarks }}
                        <br>
                        <small>{{ $resolution->created_at?->format('d-m-Y H:i') ?? 'N/A' }}</small>
                    </div>
                    </div>
                @endif
            @endforeach
           
        
        </div>
        </div>
         <!-- New Resolution Form (Fixed at the Bottom) -->
    <div class="card-footer">
            <div class="input-group">
                <textarea class="form-control" name="resolution_remarks" rows="2" placeholder="Enter new resolution..."></textarea>
               
            </div>
        </form>
    </div>
        </div>
</div>

    </div>
    
            <div class="row mt-3">
        <div class="col float-end">
        <div class="col float-end">
        <a href="#" class="btn btn-danger float-end m-3" data-bs-toggle="modal" data-bs-target="#assignTicketModal" data-id="{{ $ticket->id }}" title="Assign Ticket">
<i class='bx bx-send'></i> Escalate
                                                            </a>
            <button type="submit" name="action" value="closed" class="btn btn-primary float-end m-3">
                <i class='bx bx-window-close'></i> Close Ticket
            </button>
            @php
    $user = Auth::user();
    $user_groups = $user->user_group;
@endphp

@if($user_groups && $user_groups->group_name === 'Noc field Engineer')
            <button type="submit" name="action" value="update" class="btn btn-primary float-end m-3">
                <i class='bx bx-window-close'></i> FE Update
            </button>
            @else
            <button type="submit" name="action" value="resolve" class="btn btn-primary float-end m-3">
                <i class='bx bx-window-close'></i> NOC Update
            </button>
            @endif
        </div>
        <div class="modal fade" id="assignTicketModal" tabindex="-1" aria-labelledby="assignTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignTicketModalLabel">Escalate Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modala-body">
                <form id="assignTicketForm" action="{{ route('NocescalationGroup') }}" method="POST">
                    @csrf
                    <!-- Hidden field to store the ticket ID -->
                    <input type="hidden" name="ticket_id" id="ticket_id" value="{{ $ticket->id }}">
                    <div class="row">
                    <div class="input-group">
                <textarea class="form-control" name="resolution_remarks" rows="2" placeholder="Enter new resolution..." required></textarea>
            </div>
                    </div>
                    <div class="mt-3 mb-3">
                        <label for="user" class="form-label">Select User Group</label>
                        <select class="form-select" id="user" name="escalation_group" required>
                            <option value="" selected disabled>Select a User Group</option>
                            @foreach($user_group as $user)
                                <option value="{{ $user->id }}">{{ $user->group_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger" form="assignTicketForm">Escalate</button>
            </div>
        </div>
    </div>
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
