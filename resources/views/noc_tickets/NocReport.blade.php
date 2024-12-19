<!DOCTYPE html>
<html>
<head>
    <title>Customer Support | CRM</title>
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
<div class="row mt-3">
            <div class="col">
                <div class="card bg-body shadow-sm">
                    <div class="card-header p-3 text-white" style="background:#0A2558;">
                        <div class="row">
                            <div class="col">
                        Main Report <span class="badge badge-danger"> {{$allTickets}}</span>
                </div>
                <div class="col float-left">
                <button type="button" class=" float-end btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <i class='bx bx-filter'></i> Filter
</button>  
    </div>
    <div class="col-2">
    <button onclick="exportTableToExcel('my-table', 'noc-main-report.xlsx')" class="btn btn-primary float-end">
    <i class='bx bxs-file-export'></i> Excel
</button>

</div>
            </div>
            </div>
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
                                <div class="col">
                                <label for="filterField" class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="{{ old('start_date', request('start_date')) }}" required>
                                </div>
                                <div class="col">
                                <label for="filterField" class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="{{ old('end_date', request('end_date')) }}" required>
                                </div>
</div>
                        <div class="row mt-3">
                        <div class="col">
                            <label class="form-label">Site Name</labe>
                            <input type="text" class="form-control" name="site_name">
                        </div>
                      
                        <div class="col">
                            <label class="form-label">Ticket Number</labe>
                            <input type="text" class="form-control" name="case_id">
                        </div>
                   
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                            <label for="ticketStatus" class="form-label">Ticket Status</label>
                            <select class="form-control" id="ticketStatus" name="ticket_status">
                                <option value="" {{ request('ticket_status') == '' ? 'selected' : '' }}>All</option>
                                <option value="open" {{ request('ticket_status') == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="inprogress" {{ request('ticket_status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                                <option value="closed" {{ request('ticket_status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            </div>
                            <div class="col">
                            <label for="closedBy" class="form-label">Fault Type</label>
                            <select class="form-control" id="closedBy" name="sla_id">
                                <option value="" {{ request('complaint_detail') == '' ? 'selected' : '' }}>All</option>
                                @foreach($faults as $fault)
                                    <option value="{{ $fault->id }}">{{ $fault->fault_type }}</option>
                                @endforeach
                            </select>
                            </div>
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
            <div class="card-body" style="height:750px">
            <div class="row ">
                            <div class="outer-wrapper">
                                <div class="table-wrapper">
                                @if($tickets ->isEmpty())
            <p class="text-center">No Open Tickets</p>
        @else
                                    <table class="table " id="my-table">
                                    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">
        Ticket ID
   </th>
      <th scope="col">Logged Date</th>
      <th scope="col">Closed Date</th>
      <th scope="col">Site Name</th>
      <th scope="col">Fault Severity</th>
      <th scope="col">Resolution Time(hrs)</th>
      <th scope="col">Time taken (hrs)</th>
      <th scope="col">Priority</th>
      <th scope="col">Fault Occurrence Time</th>
      <th scope="col">Outage duration (Days)</th>
      <th scope="col">Fault Type</th>
      <th scope="col">Fault Description</th>
      <th scope="col">Root Cause</th>
      <th scope="col">Ticket Status</th>
      <th scope="col">Assigned Engineer</th>
      <th scope="col">Assigned By</th>
      <th scope="col">Escalated To</th>
      <th scope="col">Escalation Date</th>
      <th scope="col">Resoltion</th>
      <th scope="col">Sla Compliance</th>
    </tr>
  </thead>
  <tbody>
  @foreach($tickets  as $ticket) 
    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                    
                                        <td>
                <a href="{{ route('noc_tickets.showAllTickets', $ticket->id) }}" class="custom-link" title="Ticket Id">{{ $ticket->case_id }}</a>
            </td>
                                        <td>{{ $ticket->created_at }}</td>
                                        <td>@if($ticket->ticket_status == 'closed')
                                            {{ $ticket->closed_date }}
                                            
                                            @else
                                                    NA
                                            @endif</td>
                                        <td>{{ $ticket->site_name }}</td>
                                        <td>{{ $ticket->fault_severity}}</td>
                                        <td>{{ $ticket->faulty_type->ttr_in_hour }} hrs</td>
                                        
                                        <td>
                                        @if($ticket->ticket_status == 'closed')
                                            {{$ticket->time_taken}} hrs
                                            
                                            @else
                                                NA
                                            @endif
                 </td>
                                        <td>
                                            @if($ticket->faulty_type)
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
                                                <span class="badge badge-secondary">N/A</span> <!-- If no SLA or category -->
                                            @endif
                </td>
                                        <td>{{ $ticket->fault_occurrence_time}}</td>
                                        <td>{{ $ticket->outage_duration}}</td>
                                        <td>{{ $ticket->faulty_type->fault_type }}</td>
                                        <td>{{ $ticket->fault_description }}</td>
                                        <td>{{ $ticket->root_cause}}</td>
                                        <td>{{ $ticket->ticket_status}}</td>
                                        <td>
    {{ $ticket->noc_assigned_tickets->first()?->engineer?->fname . ' ' . $ticket->noc_assigned_tickets->first()?->engineer?->lname ?? 'N/A' }}
</td>
<td>
    {{ $ticket->noc_assigned_tickets->first()?->assigner?->fname . ' ' . $ticket->noc_assigned_tickets->first()?->assigner?->lname ?? 'N/A' }}
</td>
<td>
            {{ $ticket->user_group ? $ticket->user_group->group_name : 'No Group Assigned' }}
        
        </td>

<td>{{ $ticket->escalation_date}}</td>
<td>
@if($ticket->noc_resolutions->isNotEmpty())
    {{ $ticket->noc_resolutions->last()->resolution_remarks ?? 'No Closing remarks' }}
@else
    <p>No resolutions available for this ticket.</p>
@endif
</td>
<td>{{$ticket->sla_compliance}}</td>


                                        
                                                    </tr>
                                                    @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                         

                        <!-- Modal -->
                        
    </div>
</div>

@include('dashboard.script')

<!-- Bootstrap and JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function exportTableToExcel(tableID, filename = ''){
    var table = document.getElementById(tableID);
    var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    return XLSX.writeFile(wb, filename || ('ExportedData.xlsx'));
} 
    window.onload = function() {
        document.getElementById('search-focus').focus();
    };

    document.getElementById('search-focus').addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        const rows = document.querySelectorAll('#my-table tbody tr');
        let visibleRowCount = 0;

        rows.forEach(row => {
            const cells = row.querySelectorAll('td, th');
            let rowText = '';

            cells.forEach(cell => {
                rowText += cell.textContent.toLowerCase();
            });

            if (rowText.includes(searchText)) {
                row.style.display = '';
                visibleRowCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noResultsRow = document.getElementById('no-results');
        if (visibleRowCount === 0) {
            if (!noResultsRow) {
                const tbody = document.querySelector('#my-table tbody');
                const noResults = document.createElement('tr');
                const noResultsCell = document.createElement('td');
                noResultsCell.colSpan = document.querySelectorAll('#my-table thead th').length;
                noResultsCell.textContent = 'Searched data not found';
                noResultsCell.style.textAlign = 'center';
                noResults.id = 'no-results';
                noResults.appendChild(noResultsCell);
                tbody.appendChild(noResults);
            } else {
                noResultsRow.style.display = '';
            }
        } else {
            if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }
    });

    document.getElementById('assignTicketModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const ticketId = button.getAttribute('data-id');
        const idInput = document.getElementById('id');
        idInput.value = ticketId;
    });
</script>
</x-app-layout>
</body>
</html>
