
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
                <form action="{{ route('setting.update', $users->id) }}" method="POST">
                @csrf

              
    <div class="row">
        <div class="col">
            <label>First Name</label>
            <input type="text" class="form-control bg-body shadow-sm @error('fname') is-invalid @enderror" name="fname" value="{{ old('fname', $users->fname) }}" required autocomplete="off">
            @error('fname')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col">
        <label>Last Name</label>
            <input type="text" class="form-control bg-body shadow-sm @error('lname') is-invalid @enderror" name="lname" value="{{ old('lname', $users->lname) }}" required autocomplete="off">
            @error('lname')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div> 
    </div>
    <div class="row mt-3">
    <div class="col">
        <label>Mobile Number</label>
            <input type="text" class="form-control bg-body shadow-sm @error('mobile') is-invalid @enderror" name="mobile" value="{{ old('mobile', $users->mobile) }}" required autocomplete="off">
            @error('mobile')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col">
        <label>Work Place</label>
            <input type="text" class="form-control bg-body shadow-sm @error('work_location') is-invalid @enderror" name="work_location" value="{{ old('work_location', $users->work_location) }}" required autocomplete="off">
            @error('work_location')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col">
        <label>Email</label>
        <input type="email" class="form-control bg-body shadow-sm @error('email') is-invalid @enderror" name="email" value="{{ old('email',$users->email) }}" required autocomplete="off">
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <div class="row mt-3">
    <div class="col">
        <label>Location</label>
        <select class="form-control bg-body shadow-sm @error('location') is-invalid @enderror" name="location" required autocomplete="off">
                <option>Select User work Location</option>
                <option value="head_quators" {{ old('wloction', $users->location) == 'head_quators' ? 'selected' : '' }}>Head Quators</option>
                <option value="call_center" {{ old('location', $users->location) == 'call_center' ? 'selected' : '' }}>Call Center</option>
                <option value="stores" {{ old('location', $users->location) == 'stores' ? 'selected' : '' }}>Stores</option>
    </select>
            @error('location')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col">
        <label>User Group</label>
        <select class="form-control bg-body @error('group_name') is-invalid @enderror" name="group_id" required autocomplete="off">
    <option>Select Group</option>
    @foreach($groups as $group)
        <option value="{{ $group->id }}" 
            {{ old('group_id', $users->group_id) == $group->id ? 'selected' : '' }}>
            {{ $group->group_name }}
        </option>
    @endforeach
</select>
            @error('group_name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col">
        <label>Password</label>
            <input type="password" class="form-control bg-body shadow-sm @error('password') is-invalid @enderror" name="password" value="{{ old('password',$users->password) }}" required autocomplete="off">
            @error('password')
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
