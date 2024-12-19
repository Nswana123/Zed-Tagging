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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>


    <style>
        </style>
</head>
<body>

 
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
                <div class="col-9">
                Main Report <span class="badge badge-danger">{{$ticketCount}}</span>
                </div>
           
                <div class="col-1 ">
                <button type="button" class=" float-end btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <i class='bx bx-filter'></i> Filter
</button>  
    </div>
    <div class="col-2">
    <button onclick="exportTableToExcel('my-table', 'tickets.xlsx')" class="btn btn-primary float-end">
    <i class='bx bxs-file-export'></i> Excel
</button>

</div>
  

            </div>
            </div>
            <!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <h5 class="modal-title" id="filterModalLabel">Filter Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Your filter options go here -->
                    <form method="GET" action="">
                    <div class="row">
    <!-- Select date type (created_at or closed_at) -->
    <div class="col-12 mb-3">
        <label for="dateType" class="form-label">Filter By</label>
        <select class="form-select" id="dateType" name="date_type" required>
            <option value="created_at" {{ old('date_type', request('date_type')) == 'created_at' ? 'selected' : '' }}>Created Date</option>
            <option value="closed_date" {{ old('date_type', request('date_type')) == 'closed_date' ? 'selected' : '' }}>Closed Date</option>
        </select>
    </div>
</div>

<div class="row">
    <!-- Start Date -->
    <div class="col">
        <label for="startDate" class="form-label">Start Date</label>
        <input type="date" class="form-control" id="startDate" name="start_date" value="{{ old('start_date', request('start_date', '2024-09-01')) }}" required>
    </div>
    
    <!-- End Date -->
    <div class="col">
        <label for="endDate" class="form-label">End Date</label>
        <input type="date" class="form-control" id="endDate" name="end_date" value="{{ old('end_date', request('end_date', now()->toDateString())) }}" required>
    </div>
</div>
 <div class="row mt-3">
                        <div class="col">
                            <label for="ticketStatus" class="form-label">Ticket Status</label>
                            <select class="form-control" id="ticketStatus" name="ticket_status">
                                <option value="" {{ request('ticket_status') == '' ? 'selected' : '' }}>All</option>
                                <option value="open" {{ request('ticket_status') == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="inprogress" {{ request('ticket_status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                                <option value="escalated" {{ request('ticket_status') == 'escalated' ? 'selected' : '' }}>Escalated</option>
                                <option value="closed" {{ request('ticket_status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="col">
                            <label for="interactionStatus" class="form-label">FCR/Escalated</label>
                            <select class="form-control" id="interactionStatus" name="interaction_status">
                                <option value="" {{ request('interaction_status') == '' ? 'selected' : '' }}>All</option>
                                <option value="Resolved" {{ request('interaction_status') == 'Resolved' ? 'selected' : '' }}>FCR</option>
                                <option value="Escalated" {{ request('interaction_status') == 'Escalated' ? 'selected' : '' }}>Escalated</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                    <div class="col">
                    <label for="ticketedBy" class="form-label">Ticketed By</label>
                    <select class="form-control" id="ticketedBy" name="user_id">
                        <option value="" {{ request('user_id') == '' ? 'selected' : '' }}>All</option>
                        @foreach($ticket_by as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->fname }} {{ $user->lname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                        
                        <div class="col">
                            <label for="closedBy" class="form-label">Closed By</label>
                            <select class="form-control" id="closedBy" name="closed_by">
                                <option value="" {{ request('closed_by') == '' ? 'selected' : '' }}>All</option>
                                @foreach($closed_by as $user)
                                    <option value="{{ $user->id }}">{{ $user->fname }} {{ $user->lname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                    <div class="col">
            <label for="workLocation" class="form-label">Location</label>
            <select class="form-control" id="workLocation" name="location">
                <option value="" {{ request('location') == '' ? 'selected' : '' }}>All</option>
                <option value="call_center" {{ request('location') == 'call_center' ? 'selected' : '' }}>Call Center</option>
                <option value="stores" {{ request('location') == 'stores' ? 'selected' : '' }}>Stores</option>
            </select>
        </div>
                        <div class="col">
                        <label for="ticketedBy" class="form-label">Work Place</label>
            <select class="form-control" id="ticketedBy" name="work_location">
                <option value="" {{ request('work_location') == '' ? 'selected' : '' }}>All</option>
                @foreach($work as $users)
                    <option value="{{ $users->work_location }}" {{ request('work_location') == $users->work_location ? 'selected' : '' }}>
                        {{ $users->work_location }}
                    </option>
                @endforeach
            </select>
                      
                        </div>
                    </div>
                    <div class="row mt-3">
                    <div class="col">
                    <label for="cat_name" class="form-label">Complaint Name</label>
                    <select class="form-control" id="cat_name" name="cat_name">
    <option value="" {{ request('cat_name') == '' ? 'selected' : '' }}>All</option>
    @foreach($complaint_name as $cat_name)
        <option value="{{ $cat_name->ids }}" {{ request('cat_name') == $cat_name->ids ? 'selected' : '' }}>
            {{ $cat_name->category_name }} 
        </option>
    @endforeach
</select>

        </div>
                    <div class="col">
                    <label for="closedBy" class="form-label">Complaint Detail</label>
                            <select class="form-control" id="closedBy" name="cat_id">
                                <option value="" {{ request('complaint_detail') == '' ? 'selected' : '' }}>All</option>
                                @foreach($complaint_detail as $ticket)
                                    <option value="{{ $ticket->id }}">{{ $ticket->category_detail }}</option>
                                @endforeach
                            </select>
        </div>
                    </div>
                    <div class="row mt-3">
    <div class="col">
        <label for="escalationGroup" class="form-label">Escalation Group</label>
        <select class="form-control" id="escalationGroup" name="escalation_group">
            <option value="" {{ request('escalation_group') == '' ? 'selected' : '' }}>All</option>
            @foreach($escalation_group as $escalation)
                <option value="{{ $escalation->id }}" {{ request('escalation_group') == $escalation->id ? 'selected' : '' }}>
                    {{ $escalation->group_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col">
        <label for="ticketAge" class="form-label">Ticket Age</label>
        <select class="form-control" id="ticketAge" name="ticket_age">
            <option value="" {{ request('ticket_age') == '' ? 'selected' : '' }}>All</option>
            <option value="Within Time" {{ request('ticket_age') == 'Within Time' ? 'selected' : '' }}>Within Time</option>
            <option value="Out Of Time" {{ request('ticket_age') == 'Out Of Time' ? 'selected' : '' }}>Out Of Time</option>
        </select>
    </div>
</div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
                </form>
    </div>
  </div>
            </div>
</div>
            <div class="card-body" style="height:750px">
            <div class="row ">
                            <div class="outer-wrapper">
                                <div class="table-wrapper">
                                @if($tickets->isEmpty())
            <p class="text-center">No Available Data</p>
        @else
                                    <table class="table " id="my-table">
                  
                       <tr>
      <th scope="col">#</th>
      <th scope="col">
        Ticket ID
   </th>
      <th scope="col">Logged Date</th>
      <th scope="col">Closed Date</th>
      <th scope="col">MSISDN</th>
      <th scope="col">Alternative No.</th>
      <th scope="col">FullName</th>
      <th scope="col">Method of Contact</th>
      <th scope="col">Contact</th>
      <th scope="col">Product Bought</th>
      <th scope="col">Service Level(hrs)</th>
      <th scope="col">Time Taken(hrs)</th>
      <th scope="col">Priority</th>
      <th scope="col">Issue Detail</th>
      <th scope="col">Issue Category</th>
      <th scope="col">Issue Detail</th>
      <th scope="col">Issue Description</th>
      <th scope="col">Interaction Status</th>
      <th scope="col">Ticket Quality</th>
      <th scope="col">Customer Device</th>
      <th scope="col">province</th>
      <th scope="col">Town</th>
      <th scope="col">LandMark</th>
      <th scope="col">D.O.E</th>
      <th scope="col">Resolution</th>
      <th scope="col">Ticket Status</th>
      <th scope="col">Root Cause</th>
      <th scope="col">Opened By</th>
      <th scope="col">Claimed By</th>
      <th scope="col">Closed By</th>
      <th scope="col">Escalation Group</th>
      <th scope="col">Ticket Age</th>

    </tr>
  </thead>
  <tbody>
  @foreach($tickets as $ticket)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                    
                                        <td>
                <a href="{{ route('ticketing.showallTicket', $ticket->id) }}" class="custom-link" title="Ticket Id">{{ $ticket->case_id }}</a>
            </td>
                                        <td>{{ $ticket->created_at }}</td>
                                        <td>
                                            
                                        @if($ticket->ticket_status == 'closed')
                                            {{ $ticket->closed_date }}
                                            
                                            @else
                                                    NA
                                            @endif
                                        </td>
                                        <td>{{ $ticket->msisdn }}</td>
                                        <td>{{ $ticket->primary_no }}</td>
                                        <td>{{ $ticket->title }} {{ $ticket->fname }} {{ $ticket->lname }}</td>
                                        <td>{{ $ticket->method_of_contact ?? 'N/A' }}</td>
                                        <td>{{ $ticket->contact ?? 'N/A' }}</td>
                                        <td>{{ $ticket->products->product ?? 'N/A' }}</td>
                                        <td>{{ $ticket->ticket_category->ticket_sla->ttr_in_hour }} hrs</td>
                                        <td>
                                        @if($ticket->ticket_status == 'closed')
                                            {{$ticket->time_taken}} hrs
                                            
                                            @else
                                                NA
                                            @endif
                                       
                 </td>
                                        <td>
                                            @if($ticket->ticket_category && $ticket->ticket_category->ticket_sla)
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
                                                <span class="badge badge-secondary">N/A</span> <!-- If no SLA or category -->
                                            @endif
                </td>
                <td>{{ $ticket->ticket_category->category_type }}</td>
                                        <td>{{ $ticket->ticket_category->category_name ?? 'N/A' }}</td>
                                        <td>{{ $ticket->ticket_category->category_detail ?? 'N/A' }}</td>
                                        <td>{{ $ticket->issue_description}}</td>
                                        <td>{{ $ticket->interaction_status}}</td>
                                        <td>
  {{$ticket->ticket_quality}}
</td>
                                        <td>{{ optional($ticket->device)->brand ?? 'N/A' }} {{ optional($ticket->device)->model ?? '' }}</td>
                                        <td>{{ optional($ticket->customerLocation)->province ?? 'N/A' }}</td>
                                        <td>{{ optional($ticket->customerLocation)->town ?? 'N/A' }}</td>
                                        <td>{{ optional($ticket->customerLocation)->landmark ?? 'N/A' }}</td>

                                        <td>{{ $ticket->duration_of_experience}}</td>
                                        <td>
    
                                        @foreach($ticket->ticket_resolutions as $resolution)
    {{ $resolution->resolution_remarks ?? 'No Clossing remarks' }}
@endforeach

                                    </td>
                                        <td>{{ $ticket->ticket_status }}</td>
                                        <td>{{ $ticket->root_cause }}</td>
                                        <td>
                       {{$ticket->user->fname}} {{$ticket->user->lname}}
            </td>
            <td>
                       {{ optional($ticket->claimer)->fname ?? ''}} {{ optional($ticket->claimer)->lname ?? ''}}
            </td>
            <td>
                @if($ticket->ticket_status == 'closed')
                    {{$ticket->closedBy->fname}} {{$ticket->closedBy->lname}}
                
                @else
                    NA
                @endif
                     
            </td>
            <td>{{ optional($ticket->user_group)->group_name ?? 'Back Office' }}</td>
            <td>{{ $ticket->ticket_age}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                                    </table>

                               
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
@include('dashboard.script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script> 
function exportTableToExcel(tableID, filename = ''){
    var table = document.getElementById(tableID);
    var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    return XLSX.writeFile(wb, filename || ('ExportedData.xlsx'));
} 
    </script>

</body>
</html>
