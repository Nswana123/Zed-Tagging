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
        @php
                    $user = Auth::user();
                    $user_group = $user->user_group;
                @endphp

                <!-- Super Admin Links -->
                @if($user_group && in_array($user_group->group_name, ['IT Team', 'Network Team', 'KYC Team', 'Accounts','super admin']))
        <div class="row">
            <div class="col">
                <div class="card bg-body shadow-sm">
                    <div class="card-header p-3 text-white" style="background:#0A2558;">
                        <div class="row">
                            <div class="col">
                        Escalated Tickets <span class="badge badge-danger">{{$EscalatedCount}}</span>
                </div>
                <div class="col float-left">
                <div class="input-group ">
    <input id="search-focus" type="search" id="form1" class="form-control" placeholder="Search Case Id/Number" />
    
  </div>
                </div>
            </div>
            </div>
            <div class="card-body" style="height:750px">
            <div class="row ">
                            <div class="outer-wrapper">
                                <div class="table-wrapper">
                                @if($tickets->isEmpty())
            <p class="text-center">No Open Tickets</p>
        @else
                                    <table class="table " id="my-table">
                                    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">
        Ticket ID
   </th>
      <th scope="col">Logged Time</th>
      <th scope="col">MSISDN</th>
      <th scope="col">FullName</th>
      <th scope="col">SL(hrs)</th>
      <th scope="col">Time Remaining</th>
      <th scope="col">Priority</th>
      <th scope="col">Issue Category</th>
      <th scope="col">Issue Detail</th>
      <th scope="col">Issue Description</th>
      <th scope="col">Ticket Status</th>
      <th scope="col">Claimer</th>
    </tr>
  </thead>
  <tbody>
  @foreach($tickets as $ticket)
  @php
            // Get the latest ticket resolution if it exists
            $latestResolution = $ticket->ticket_resolutions->last(); // Get the latest resolution
        @endphp
       
        <tr class="
            {{ $latestResolution && $latestResolution->closed == 'no' ? 'table-primary' : '' }} 
            {{ $latestResolution && $latestResolution->closed == 'closed' ? 'table-success' : '' }} 
               {{ $latestResolution && $latestResolution->closed == 'comment' ? 'table-primary' : '' }} 
              {{ $ticket->refund == 'yes' ? 'table-danger' : '' }} 
        ">
                                        <td>{{ $loop->iteration }}</td>
                                    
                                        <td>
                <a href="{{ route('ticketing.showEscalatedTickets', $ticket->id) }}" class="custom-link" title="Ticket Id">{{ $ticket->case_id }}</a>
            </td>
                                        <td>{{ $ticket->created_at }}</td>
                                        <td>{{ $ticket->msisdn }}</td>
                                        <td>{{ $ticket->title }} {{ $ticket->fname }} {{ $ticket->lname }}</td>
                                        <td>{{ $ticket->ticket_category->ticket_sla->ttr_in_hour }} hrs</td>
                                        <td>
                                            @if(isset($ticket->time_remaining))
                                                {{ $ticket->time_remaining['hours'] }}h {{ $ticket->time_remaining['minutes'] }}m
                                            @else
                                                N/A
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
                                        <td>{{ $ticket->ticket_category->category_name ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($ticket->ticket_category->category_detail ?? 'N/A', 20, ' ...') }}</td>
                                        <td>{{ Str::limit($ticket->issue_description, 20, ' ...') }}</td>
                                       
                                        <td>{{ $ticket->ticket_status }}</td>
                                        <td>
                                        @if ($ticket->user_tickets->isNotEmpty())
                @php
                    $user_tickets = $ticket->user_Tickets->first(); // Or use ->last() if you want the latest one
                @endphp

                @if ($user_tickets->assignment_status === 'open')
                    {{ optional($user_tickets->claimer)->fname }} {{ optional($user_tickets->claimer)->lname }}
                @else
                    Assign By: {{ optional($user_tickets->assigner)->fname }} {{ optional($user_tickets->assigner)->lname }}
                @endif
            @else
                No assignments found
            @endif

                                        </td>
                                                       
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
@else
<div class="row mt-3">
    <div class="col">
        <div class="card bg-body shadow-sm">
            <div class="card-header p-3 text-white float-centre" style="background:#0A2558;">
            <div class="row">
                <div class="col">
               All Escalations  <span class="badge badge-danger">{{$allEscalatedCount }}</span>
                </div>
                <div class="col float-left">
                <div class="input-group ">
    <input id="search-focus" type="search" id="form1" class="form-control" placeholder="Search Case Id/Number" />
    
  </div>
                </div>
            </div>
            </div>
            <div class="card-body" style="height:750px">
            <div class="row ">
                            <div class="outer-wrapper">
                                <div class="table-wrapper">
                                @if($allEscalated ->isEmpty())
            <p class="text-center">No Available Tickets</p>
        @else
                                    <table class="table " id="my-table">
                                    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">
        Ticket ID
   </th>
      <th scope="col">Logged Time</th>
      <th scope="col">MSISDN</th>
      <th scope="col">FullName</th>
      <th scope="col">SL(hrs)</th>
      <th scope="col">Time Remaining</th>
      <th scope="col">Priority</th>
      <th scope="col">Issue Category</th>
      <th scope="col">Issue Detail</th>
      <th scope="col">Issue Description</th>
      <th scope="col">Escalation Group</th>
      <th scope="col">Ticket Status</th>
    </tr>
  </thead>
  <tbody>
  @foreach($allEscalated as $ticket)
  <tr class="
         @if ($ticket->ticket_resolutions->contains('opened', 'no'))
       table-danger
    @else ($ticket->ticket_resolutions->contains('closed', 'closed'))
         table-success
    @endif
    ">
                                        <td>{{ $loop->iteration }}</td>
                                    
                                        <td>
                <a href="{{ route('ticketing.showInboTickets', $ticket->id) }}" class="custom-link" title="Ticket Id">{{ $ticket->case_id }}</a>
            </td>
                                        <td>{{ $ticket->created_at }}</td>
                                        <td>{{ $ticket->msisdn }}</td>
                                        <td>{{ $ticket->title }} {{ $ticket->fname }} {{ $ticket->lname }}</td>
                                        <td>{{ $ticket->ticket_category->ticket_sla->ttr_in_hour }} hrs</td>
                                        <td>
                                            @if(isset($ticket->time_remaining))
                                                {{ $ticket->time_remaining['hours'] }}h {{ $ticket->time_remaining['minutes'] }}m
                                            @else
                                                N/A
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
                                        <td>{{ $ticket->ticket_category->category_name ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($ticket->ticket_category->category_detail ?? 'N/A', 20, ' ...') }}</td>
                                        <td>{{ Str::limit($ticket->issue_description, 20, ' ...') }}</td>
                                        <td>{{ optional($ticket->user_group)->group_name ?? 'Back Office' }}</td>
                                        <td>{{ $ticket->ticket_status }}</td>
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
@endif
@include('dashboard.script')

<!-- Bootstrap and JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
