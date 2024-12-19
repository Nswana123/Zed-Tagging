
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
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
<div class="row">
    <div class="col">
        <div class="card bg-body shadow-sm">
            <div class="card-header p-3 text-white float-centre" style="background:#0A2558;">
            <div class="row">
            <div class="col-2">
                All Refunds <span class="badge badge-warning">{{$allrefunds}}</span>
    </div>
    <div class="col-2">
                Refunded  <span class="badge badge-success">{{$refunded}}</span>
    </div>
    <div class="col-2 ">
    Unfunded <span class="badge badge-danger">{{$Notrefunded}}</span> 
    </div>

    <div class="col-4">
    <button onclick="exportTableToExcel('my-table', 'tickets.xlsx')" class="btn btn-primary float-end">
    <i class='bx bxs-file-export'></i> Export to Excel
</button>
    </div>
    <div class="col-2">
    
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
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
                                    <label class="form-label">MSISDN</labe>
                                    <input type="text" class="form-control" name="msisdn">
                                </div>
                            
                                <div class="col">
                                    <label class="form-label">Ticket Number</labe>
                                    <input type="text" class="form-control" name="case_id">
                                </div>
                        </div>
                         
                            <div class="row mt-3">
                              
                                    <label>Refund Status
                                    <select class="form-control" name="refund_status">
    <option value="" {{ request('refund') == '' ? 'selected' : '' }}>All</option>
    <option value="not_refunded" {{ request('refund_status') == 'not_refunded' ? 'selected' : '' }}>Not Refunded</option>
    <option value="refunded" {{ request('refund_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
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
            <div class="card-body" style="height:750px">
            <div class="row ">
                            <div class="outer-wrapper">
                                <div class="table-wrapper">
                                @if($tickets->isEmpty())
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
      <th scope="col">Alternative</th>
      <th scope="col">FullName</th>
      <th scope="col">SL(hrs)</th>
      <th scope="col">Time Remaining</th>
      <th scope="col">Priority</th>
      <th scope="col">Issue Category</th>
      <th scope="col">Issue Detail</th>
      <th scope="col">Issue Description</th>
      <th scope="col">Refund Status</th>
      <th scope="col">Ticket Status</th>
    </tr>
  </thead>
  <tbody>
  @foreach($tickets as $ticket)
  <tr class="
        {{ $ticket->refund_status == 'refunded' ? 'table-success' : '' }} 
          {{ $ticket->refund_status == 'not_refunded' ? 'table-danger' : '' }} 
    ">
                                        <td>{{ $loop->iteration }}</td>
                                    
                                        <td>
                <a href="{{ route('ticketing.showRefundList', $ticket->id) }}" class="custom-link" title="Ticket Id">{{ $ticket->case_id }}</a>
            </td>
                                        <td>{{ $ticket->created_at }}</td>
                                        <td>{{ $ticket->msisdn }}</td>
                                        <td>{{ $ticket->primary_no }}</td>
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
                                        <td>{{ $ticket->ticket_category->category_detail ?? 'N/A' }}</td>
                                        <td>{{$ticket->issue_description }}</td>
                                        <td>{{$ticket->refund_status }}</td>
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
@include('dashboard.script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <script>
function exportTableToExcel(tableID, filename = ''){
    var table = document.getElementById(tableID);
    var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    return XLSX.writeFile(wb, filename || ('ExportedData.xlsx'));
}
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
