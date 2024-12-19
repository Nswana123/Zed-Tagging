
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
                <form action="{{route('createMessages')}}" method="POST">
                @csrf

              
   
   
    <div class="row mt-3">
    <div class="col-3">
        <label>MSISDN</label>
        <input type="text" class="form-control bg-body shadow-sm" name="msisdn" value="{{ old('msisdn') }}" required autocomplete="off">
            @error('msisdn')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-3">
        <label>Message Name</label>
        <select class="form-control bg-body @error('message_id') is-invalid @enderror" name="message_id" id="messageSelect" required autocomplete="off">
            <option>Select Message Name</option>
            @foreach($smss as $sms)
                <option value="{{ $sms->id }}" 
                    data-description="{{ $sms->message }}" 
                    {{ old('message_id') == $sms->id ? 'selected' : '' }}>
                    {{ $sms->name }}
                </option>
            @endforeach
        </select>
        @error('message_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col">
        <label>Message Description</label>
        <textarea class="form-control bg-body shadow-sm" name="message" id="messageDescription" rows="3">{{ old('message') }}</textarea>
        @error('message')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>

    

    <div class="row mt-3">
        <div class="col">
            <button type="submit" class="btn btn-primary float-end">Save Message</button>
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
                       All Created Messages
                       <span class="badge badge-danger">{{$messageCount }}</span>
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
        <input type="date" class="form-control" name="start_date" value="{{ old('start_date', request('start_date', '2024-09-01')) }}" required>
    </div>
    <div class="col">
        <label for="end_date" class="form-label">End Date</label>
        <input 
            type="date" class="form-control" name="end_date" value="{{ old('end_date', request('end_date', now()->toDateString())) }}" required>
    </div>
</div>
                        <div class="row mt-3">
                     
                            <label class="form-label">MSISDN</labe>
                            <input type="text" class="form-control" name="msisdn">
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
            <div class="card-body" style="height:650px">
                <div class="row">
                    <div class="outer-wrapper">
                        <div class="table-wrapper">
                           
                                <table class="table" id="my-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">MSISDN</th>
                                            <th scope="col">Message Name</th>
                                            <th scope="col">Message Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach( $messages as $sms)
                                    <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $sms->msisdn }}</td>
                                    <td>{{ $sms->messages?->name ?? 'N/A' }}</td>
                                        <td>{{ $sms->message ?? 'N/A' }}</td>

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
  document.getElementById('messageSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var description = selectedOption.getAttribute('data-description'); // Get the description from the selected option
        document.getElementById('messageDescription').value = description; // Update the textarea with the description
    });

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
