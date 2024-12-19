
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
            <div class="card-header p-3 text-white float-centre" style="background:#0A2558;">
            <div class="row">
                <div class="col">
                All Open Ticket <span class="badge badge-danger">{{$allOpenTickets}}</span>
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
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
  @foreach($tickets as $ticket)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                    
                                        <td>
                <a href="{{ route('ticketing.showOpenTicket', $ticket->id) }}" class="custom-link" title="Ticket Id">{{ $ticket->case_id }}</a>
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
                                        @php
                        $user = Auth::user();
                        $user_group = $user->user_group;
                    @endphp

                    <!-- Check if the logged-in user is a super admin -->
                    @if($user_group && $user_group->group_name === 'back office')
                                 @if ($ticket->claim_status == 'open')
                            <a href="{{ route('ticketing.claim', $ticket->id) }}" class="btn btn-primary btn-sm" title="Claim Ticket">
                                <i class='bx bxs-spreadsheet'></i> Claim
                            </a>
                        @else
                            <span>Claimed by {{ $ticket->claimed_by ? $ticket->user->fname : 'someone' }}</span>
                        @endif
                        @else
                        <a href="{{ route('ticketing.showOpenTicket', $ticket->id) }}" class="btn btn-primary btn-sm" title="More Details"><i class='bx bxs-spreadsheet'></i> View</a>
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
@include('dashboard.script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script> 
   function confirmDelete() {
        return confirm('Are you sure you want to delete this user group?');
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

    // Check if any rows are visible
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
    </script>
    </x-app-layout>
</body>
</html>