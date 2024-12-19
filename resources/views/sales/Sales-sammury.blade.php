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
                <div class="col-9">
                All Sales <span class="badge badge-danger"></span>
                </div>
           
                <div class="col-1 ">
                <button type="button" class=" float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <i class='bx bx-filter'></i> Filter
</button>  
    </div>
    <div class="col-2">
    <button onclick="exportTableToExcel('my-table', 'SalesRecords.xlsx')" class="btn btn-primary float-end">
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
            <label for="ticketedBy" class="form-label">Agent Name</label>
            <select class="form-control" id="ticketedBy" name="user_id">
                <option value="" {{ request('user_id') == '' ? 'selected' : '' }}>All</option>
                @foreach($group as $users)
                    <option value="{{ $users->id }}" {{ request('user_id') == $users->id ? 'selected' : '' }}>
                        {{ $users->fname }} {{ $users->lname }}
                    </option>
                @endforeach
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
                            <label for="ticketedBy" class="form-label">Product Name</label>
            <select class="form-control" id="product_id" name="product_id">
                <option value="" {{ request('product_id') == '' ? 'selected' : '' }}>All</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->product }}
                    </option>
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
                                @if($sales->isEmpty())
            <p class="text-center">No Available Data</p>
        @else
        <table>
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Sell Date</th>
            <th scope="col">Product Name</th>
            <th scope="col">Total Sales</th>
            <th scope="col">Total Quantity</th>
            <th scope="col">Amount Cashed In (ZMW)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $sale->sell_date }}</td>
                <td>{{ $sale->product->product }}</td>
                <td>{{ $sale->total_sales }}</td>
                <td>{{ $sale->total_quantity }}</td>
                <td>{{ number_format($sale->total_cashed_in, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5"><strong>Total Amount Cashed In (ZMW):</strong></td>
            <td><strong>{{ number_format($totalAmountCashedIn, 2) }}</strong></td>
        </tr>
    </tfoot>
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
    return XLSX.writeFile(wb, filename || ('SalesRecords.xlsx'));
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
