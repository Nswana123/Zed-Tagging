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
        <div class="row">
            <div class="col">
                <div class="card bg-body shadow-sm">
                    <div class="card-header p-3 text-white" style="background:#0A2558;">
                        <div class="row">
                            <div class="col">
                        All Unassigned Tickets<span class="badge badge-danger">{{$allOpenTickets}}</span>
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
      <th scope="col">Site Name</th>
      <th scope="col">Fault Severity</th>
      <th scope="col">Resolution Time(hrs)</th>
      <th scope="col">Time Remaining</th>
      <th scope="col">Priority</th>
      <th scope="col">Fault Occurrence Time</th>
      <th scope="col">Fault Type</th>
      <th scope="col">Fault Description</th>
      <th scope="col">Ticket Status</th>
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
  @foreach($tickets as $ticket)
        <tr>
                                        <td>{{ $loop->iteration }}</td>
                                    
                                        <td>
                <a href="{{ route('noc_tickets.showNocTickets', $ticket->id) }}" class="custom-link" title="Ticket Id">{{ $ticket->case_id }}</a>
            </td>
                                        <td>{{ $ticket->created_at }}</td>
                                        <td>{{ $ticket->site_name }}</td>
                                        <td>{{ $ticket->fault_severity}}</td>
                                        <td>{{ $ticket->faulty_type->ttr_in_hour }} hrs</td>
                                        <td>
                                        @if(isset($ticket->time_remaining))
                                                {{ $ticket->time_remaining['hours'] }}h {{ $ticket->time_remaining['minutes'] }}m
                                            @else
                                                N/A
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
                                        <td>{{ $ticket->faulty_type->fault_type }}</td>
                                        <td>{{ Str::limit($ticket->fault_description, 20, ' ...') }}</td>
                                      
                                        <td>{{ $ticket->ticket_status }}</td>
                                        
                                                        <td>
                                                            <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignTicketModal" data-id="{{ $ticket->id }}" title="Assign Ticket">
                                                                <i class='bx bx-transfer'></i> Assign
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                         

                        <!-- Modal -->
                        <div class="modal fade" id="assignTicketModal" tabindex="-1" aria-labelledby="assignTicketModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="assignTicketModalLabel">Assign Ticket</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="assignTicketForm" action="{{ route('assignTicket') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="ticket_id" id="id" value="">
                                            <div class="mb-3">
                                                <label for="user" class="form-label">Select User</label>
                                                <select class="form-select" id="user" name="engineer_id" required>
                                                    <option value="" selected disabled>Select a user</option>
                                                    @foreach($user_group as $user)
                                                        <option value="{{ $user->id }}">{{ $user->fname }} {{ $user->lname }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" form="assignTicketForm">Assign Ticket</button>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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