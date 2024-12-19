
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
                <form action="{{ route('category.updateTicketCat', $ticket_cat->id) }}" method="POST">
                @csrf
    <div class="row mt-3">
    <div class="col">
        <label>Ticket Priority</label>
        <select class="form-control bg-body @error('priority') is-invalid @enderror" name="sla_type_id" required autocomplete="off">
                    <option>Select Ticket Priority</option>
                    @foreach($ticket_sla as $sla)
                    <option value="{{ $sla->id }}" 
            {{ old('sla_type_id', $ticket_cat->sla_type_id) == $sla->id ? 'selected' : '' }}>
            {{ $sla->priority }} {{$sla->ttr_in_hour}}
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
            <select class="form-control bg-body shadow-sm @error('category_name') is-invalid @enderror" name="category_name" required>
                <option>Select the Complaint Category</option>
                <option value="kyc" {{ old('category_name', $ticket_cat->category_name) == 'kyc' ? 'selected' : '' }}>KYC</option>
                <option value="network" {{ old('category_name', $ticket_cat->category_name) == 'network' ? 'selected' : '' }}>Network</option>
                <option value="top up" {{ old('category_name', $ticket_cat->category_name) == 'top up' ? 'selected' : '' }}>Top Up</option>
                <option value="self care app" {{ old('category_name', $ticket_cat->category_name) == 'self care app' ? 'selected' : '' }}>Self Care App</option>
                <option value="Billing" {{ old('category_name', $ticket_cat->category_name) == 'Billing' ? 'selected' : '' }}>Billing</option>
                <option value="products" {{ old('category_name', $ticket_cat->category_name) == 'products' ? 'selected' : '' }}>Products</option>
                <option value="stolen phone/number" {{ old('category_name', $ticket_cat->category_name) == 'stolen phone/number' ? 'selected' : '' }}>Stolen phone/number</option>
                <option value="call records request" {{ old('category_name', $ticket_cat->category_name) == 'call records request' ? 'selected' : '' }}>Call Records Request</option>
                <option value="device" {{ old('category_name', $ticket_cat->category_name) == 'device' ? 'selected' : '' }}>Device</option>
                <option value="service quality" {{ old('category_name', $ticket_cat->category_name) == 'service quality' ? 'selected' : '' }}>Service Quality</option>
                <option value="ticketing" {{ old('category_name', $ticket_cat->category_name) == 'ticketing' ? 'selected' : '' }}>Ticketing</option>
                <option value="zed wallet" {{ old('category_name', $ticket_cat->category_name) == 'zed wallet' ? 'selected' : '' }}>ZED Wallet</option>
                <option value="device management" {{ old('category_name', $ticket_cat->category_name) == 'device management' ? 'selected' : '' }}>Device Management</option>
                <option value="fraud" {{ old('category_name', $ticket_cat->category_name) == 'fraud' ? 'selected' : '' }}>Fraud</option>
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
    <option value="Complaint" {{ old('category_type', $ticket_cat->category_type) == 'Complaint' ? 'selected' : '' }}>Complaint</option>
    <option value="Enquiry" {{ old('category_type', $ticket_cat->category_type) == 'Enquiry' ? 'selected' : '' }}>Enquiry</option>
    <option value="Request" {{ old('category_type', $ticket_cat->category_type) == 'Request' ? 'selected' : '' }}>Request</option>
    <option value="Ticket" {{ old('category_type', $ticket_cat->category_type) == 'Ticket' ? 'selected' : '' }}>Ticket</option>
</select>
        @error('category_type')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
<div class="col">

            <label>Complaint Type</label>
            <input type="text" class="form-control bg-body shadow-sm @error('category_detail') is-invalid @enderror" 
                   name="category_detail" value="{{ old('category_detail', $ticket_cat->category_detail) }}" required autocomplete="off">
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
