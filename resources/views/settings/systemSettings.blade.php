
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
            <form action="{{route('create-settings')}}" method="POST">
    @csrf

    <div class="row">
    <div class="col">
        <label>User Group</label>
        <select class="form-control bg-body @error('group_name') is-invalid @enderror" name="user_group_id" required autocomplete="off">
                    <option>Select Group</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_name') == $group->id ? 'selected' : '' }}>
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
        <label>User Roles</label>
        <select class="form-control bg-body @error('name') is-invalid @enderror" name="permission_id[]" multiple required autocomplete="off">
    <option disabled>Select User Roles</option>
    @foreach($permission as $permission)
        <option value="{{ $permission->id }}" {{ in_array($permission->id, old('permission_id', [])) ? 'selected' : '' }}>
            {{ $permission->name }}
        </option>
    @endforeach
</select>
       @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            <button type="submit" class="btn btn-primary float-end">Assign Roles</button>
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
                        All User Roles
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
                            @if($roles->isEmpty())
                                <p class="text-center">No Available User roles</p>
                            @else
                                <table class="table" id="my-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Group Name</th>
                                            <th scope="col">User Roles</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roles as $role)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $role->user_group->group_name }}</td>
                                                 <td>{{ $role->permissions->name }}</td> 
                                                <td>
                                                    <a href="#" class="btn btn-primary btn-sm" title="Edit Details"><i class='bx bx-edit-alt'></i> Edit</a>
                                                    <form action="{{ route('delete-settings', $role->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete();">
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
        return confirm('Are you sure you want to delete this User Role?');
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
