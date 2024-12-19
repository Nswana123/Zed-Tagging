
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
                <form action="{{route('create-ticket-sla')}}" method="POST">
                @csrf

              
   
   
    <div class="row mt-3">
    <div class="col">
        <label>Ticket priority</label>
        <select class="form-control bg-body @error('priority') is-invalid @enderror" name="priority" required autocomplete="off">
                <option>Select priority</option>
                <option value="severe" {{ old('priority') == 'severe' ? 'selected' : '' }}>Severe</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
            </select>
            @error('priority')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col">
        <label>Description</label>
        <input type="text" class="form-control bg-body shadow-sm" name="description" value="{{ old('description') }}" required autocomplete="off">
            @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col">
        <label>Resolution Time</label>
            <input type="text" class="form-control bg-body shadow-sm @error('ttr_in_hour') is-invalid @enderror" name="ttr_in_hour" value="{{ old('password') }}" required autocomplete="off">
            @error('ttr_in_hour')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    

    <div class="row mt-3">
        <div class="col">
            <button type="submit" class="btn btn-primary float-end">Save Sla</button>
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
                       Compalaint SLA
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
                                            <th scope="col">Description</th>
                                            <th scope="col">Resolution Time</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach( $slas as $sla)
                                    <tr>
                                    <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sla->priority }}</td>
                                        <td>{{ $sla->description }}</td>
                                        <td>{{ $sla->ttr_in_hour }} hours</td>
                                        <td>
                                        <a href="{{ route('category.editTicketSla', $sla->id) }}" class="btn btn-primary btn-sm" title="Edit Details"><i class='bx bx-edit-alt'></i> Edit</a>
                                                    <form action="{{ route('delete-TicketSla', $sla->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete();">
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
        return confirm('Are you sure you want to delete this Ticket Sla?');
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
