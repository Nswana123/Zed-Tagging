
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
                All Ticket <span class="badge badge-danger">{{$allTickets}}</span>
                </div>
                <div class="col float-left">
                <button onclick="exportTableToExcel('my-table', 'tickets-Quality.xlsx')" class="btn btn-primary float-end">
    <i class='bx bxs-file-export'></i> Export to Excel
</button>
                </div>
                <div class="col float-left">
           
                <button type="button" class=" float-end btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <i class='bx bx-filter'></i> Filter
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
    <div class="col">
        <label for="start_date" class="form-label">Start Date</label>
        <input 
            type="date" 
            class="form-control" 
            name="start_date" 
            value="{{ old('start_date', request('start_date', '2024-09-01')) }}" 
            required
        >
    </div>
    <div class="col">
        <label for="end_date" class="form-label">End Date</label>
        <input 
            type="date" 
            class="form-control" 
            name="end_date" 
            value="{{ old('end_date', request('end_date', now()->toDateString())) }}" 
            required
        >
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
                                <option value="" {{ request('interation_status') == '' ? 'selected' : '' }}>All</option>
                                <option value="Resolved" {{ request('interation_status') == 'Resolved' ? 'selected' : '' }}>FCR</option>
                                <option value="Escalated" {{ request('interation_status') == 'Escalated' ? 'selected' : '' }}>Escalated</option>
                            </select>
                        </div>
                    </div>
                        <div class="row mt-3">
                        <div class="col">
                                        <label for="ticketedBy" class="form-label">Agent Name</label>
                            <select class="form-control" id="ticketedBy" name="user_id">
                                <option value="" {{ request('ticket_by') == '' ? 'selected' : '' }}>All</option>
                                @foreach($group as $users)
                                    <option value="{{ $users->id }}" {{ request('ticket_by') == $users->id ? 'selected' : '' }}>
                                        {{ $users->fname }} {{ $users->lname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                      
                        <div class="col">
                        <label for="ticketedBy" class="form-label">Ticket Quality</label>
                        <select class="form-control" id="workLocation" name="ticket_quality">
                        <option value="" {{ request('ticket_quality') == '' ? 'selected' : '' }}>All</option>
                        <option value="normal" {{ request('ticket_quality') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="good" {{ request('ticket_quality') == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="bad" {{ request('ticket_quality') == 'bad' ? 'selected' : '' }}>Bad</option>
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
                                @if($QAtickets->isEmpty())
            <p class="text-center">No Available Tickets</p>
        @else
                                    <table class="table " id="my-table">
                                    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">
        Ticket ID
   </th>
      <th scope="col">Opened Date</th>
      <th scope="col">Closed Date</th>
      <th scope="col">MSISDN</th>
      <th scope="col">FullName</th>
      <th scope="col">SL(hrs)</th>
      <th scope="col">Time Taken</th>
      <th scope="col">Priority</th>
      <th scope="col">Issue Category</th>
      <th scope="col">Issue Detail</th>
      <th scope="col">Issue Description</th>
      <th scope="col">Ticket Status</th>
      <th scope="col">Opened By</th>
      <th scope="col">Closed By</th>
      <th scope="col">Ticket Quality</th>
    </tr>
  </thead>
  <tbody>
  @foreach($QAtickets as $ticket)
                                    <tr class="
        {{ $ticket->ticket_quality == 'normal' ? 'table-success' : '' }} 
         {{ $ticket->ticket_quality == 'good' ? 'table-success' : '' }} 
          {{ $ticket->ticket_quality == 'bad' ? 'table-danger' : '' }} 
    ">
                                        <td>{{ $loop->iteration }}</td>
                                    
                                        <td>
                <a href="{{ route('ticketing.showQuality', $ticket->id) }}" class="custom-link" title="Ticket Id">{{ $ticket->case_id }}</a>
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
                                        <td>{{ $ticket->title }} {{ $ticket->fname }} {{ $ticket->lname }}</td>
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
                                        <td>{{ $ticket->ticket_category->category_name ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($ticket->ticket_category->category_detail ?? 'N/A', 20, ' ...') }}</td>
                                        <td>{{ Str::limit($ticket->issue_description, 20, ' ...') }}</td>
                                        <td>{{ $ticket->ticket_status }}</td>
                                        <td>
                       {{$ticket->user->fname}} {{$ticket->user->lname}}
            </td>
            <td>
                @if($ticket->ticket_status == 'closed')
                    {{$ticket->closedBy->fname}} {{$ticket->closedBy->lname}}
                
                @else
                    NA
                @endif
                     
            </td>
            <td>
                    {{$ticket->ticket_quality}}
                     
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
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
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
