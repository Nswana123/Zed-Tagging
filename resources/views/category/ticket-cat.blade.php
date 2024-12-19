
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
            <div class="card-body">
                <form action="{{route('create-ticket-cat')}}" method="POST">
                @csrf

              
   
   
    <div class="row mt-3">
    <div class="col">
    <label>Ticket Priority</label>
    <select class="form-control bg-body @error('priority') is-invalid @enderror" name="sla_type_id" id="prioritySelect" required autocomplete="off">
        <option value="" selected disabled>Select Ticket Priority</option>
        @foreach($ticket_sla as $sla)
    <option value="{{ $sla->id }}" data-ttr="{{ $sla->ttr_in_hour }}" {{ old('priority') == $sla->id ? 'selected' : '' }}>
        {{ $sla->priority }}  {{ $sla->ttr_in_hour }}
    </option>
@endforeach
    </select>
    @error('priority')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

        <div class="col">
        <label>Complaint Category</label>
                        <select class="form-control bg-body shadow-sm" name="category_name">

                        <option>Select the Complaint Category</option>
                        <option value="kyc">KYC</option>
                        <option value="network">Network</option>
                        <option value="top up">Top Up</option>
                        <option value="self care app">Self Care App</option>
                        <option value="Billing">Billing</option>
                        <option value="products">Products</option>
                        <option value="stolen phone/number">Stolen phone / number</option>
                        <option value="call records request">Call Records Request</option>
                        <option value="device">Device</option>
                        <option value="service quality">Service Quality</option>
                        <option value="ticketing">Ticketing</option>
                        <option value="zed wallet">ZED Wallet</option>
                        <option value="device management">Device Management</option>
                        <option value="fraud">Fraud</option>
                        <option value="dropped call">Dropped Call</option>

    </select>
            @error('category_name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
<div class="row mt-3">
<div class="col mb-3">
                                            <label for="issue_type">Issue Type</label>
                                            <select class="form-control bg-body shadow-sm" name="category_type">
                                                <option value="">Select Complaint Type</option>
                                                <option value="Complaint" {{ old('category_type') == 'Complaint' ? 'selected' : '' }}>Complaint</option>
                                                <option value="Enquiry" {{ old('category_type') == 'Enquiry' ? 'selected' : '' }}>Enquiry</option>
                                                <option value="Request" {{ old('category_type') == 'Request' ? 'selected' : '' }}>Request</option>
                                                <option value="Ticket" {{ old('category_type') == 'Ticket' ? 'selected' : '' }}>Ticketing</option>
                                                <option value="Incident" {{ old('category_type') == 'Incident' ? 'selected' : '' }}>Incident</option>
                                            </select>
                                            @error('category_type')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
<div class="col">
        <label>Complaint</label>
            <input type="text" class="form-control bg-body shadow-sm" name="category_detail" value="{{ old('category_detail') }}" required autocomplete="off">
            @error('category_detail')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

</div>
    

    <div class="row mt-2">
        <div class="col">
            <button type="submit" class="btn btn-primary float-end">Submit</button>
        </div>
    </div>
    </form>
            </div>
        </div>
    </div>
</div> 
<div class="row mt-3">
    <div class="col">
        <div class="card bg-body shadow-sm">
            <div class="card-header p-3 text-white float-center" style="background:#0A2558;">
                <div class="row">
                    <div class="col">
                        All User
                    </div>
                    <div class="col float-left">
                        <div class="input-group">
                            <input id="search-focus" type="search" class="form-control" placeholder="Search Case Id/Number" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body" style="height:750px">
                <div class="row">
                    <div class="outer-wrapper">
                        <div class="table-wrapper">
                           
                                <table class="table" id="my-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Priority</th>
                                            <th scope="col">Complaint Category</th>
                                            <th scope="col">Category Type</th>
                                            <th scope="col">Complaint</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($ticket_cat as $cat)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $cat->ticket_sla->priority }}</td>   
                                        <td>{{ $cat->category_name }}</td>
                                        <td>{{ $cat->category_type }}</td>
                                        <td>{{ $cat->category_detail }}</td>
                                        <td>
                                        <a href="{{ route('category.editTicketCat', $cat->id) }}" class="btn btn-primary btn-sm" title="Edit Details"><i class='bx bx-edit-alt'></i> Edit</a>
                                                    <form action="{{ route('delete-TicketCat', $cat->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete();">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class='bx bx-trash'></i> Delete
                                                    </button>
                                                </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                               
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
</div>
@include('dashboard.script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script> 
   function confirmDelete() {
        return confirm('Are you sure you want to delete this Ticket Category?');
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
   
    document.getElementById('prioritySelect').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var ttrInHour = selectedOption.getAttribute('data-ttr');
    console.log("Selected TTR:", ttrInHour);
    document.getElementById('resolutionTime').value = ttrInHour || '';
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
