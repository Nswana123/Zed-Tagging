<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support | CRM</title>
    @include('dashboard.style')
    <!-- External CSS and Icons -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/dashboard/style.css">
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <style>
        /* Additional styling can go here */
    </style>
</head>

<body>
    <x-app-layout>
        <!-- Sidebar and Header -->
        @include('dashboard.sidebar')
        <div class="home-section">
            @include('dashboard.header')

            <!-- Main Content -->
            <div class="home-content p-3">
                <!-- Display Errors and Success Messages -->
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
                <!-- Tickets Summary Chart (Call Center) -->
                <div class="row">
                    <div class="col">
                        <div class="card bg-body shadow-sm">
                            <div class="card-header p-3 text-white" style="background:#0A2558;">
                                <div class="row">
                                    <div class="col">
                                        Tickets Summary Chart (Call Center)
                                    </div>
                                    <div class="col text-end">
                                        <div class="input-group">
                                            <input id="search-focus" type="search" class="form-control" placeholder="Search Case Id/Number">
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
                                                        <th>Date</th>
                                                        <th>Total Tickets</th>
                                                        <th>Resolved Tickets</th>
                                                        <th>Escalated Tickets</th>
                                                        <th>Open Tickets</th>
                                                        <th>Closed Tickets</th>
                                                        <th>FCR (%)</th>
                                                        <th>Within Time (%)</th>
                                                        <th>Out of Time (%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($ticketData as $date => $data)
                                                        <tr>
                                                            <td>{{ $date }}</td>
                                                            <td>{{ $data['total_tickets'] }}</td>
                                                            <td>{{ $data['resolved_tickets'] }}</td>
                                                            <td>{{ $data['escalated_tickets'] }}</td>
                                                            <td>{{ $data['open_or_inprogress_or_escalated'] }}</td>
                                                            <td>{{ $data['closed_escalated'] }}</td>
                                                            <td>{{ $data['per_fcr'] }}%</td>
                                                            <td>{{ $data['per_withintime'] }}%</td>
                                                            <td>{{ $data['per_outoftime'] }}%</td>
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
                </div>
                <!-- Service Management Section -->
                <div class="row mt-3">
                    <div class="col">
                        <div class="card bg-body shadow-sm">
                            <div class="card-header p-3 text-white" style="background:#0A2558;">
                                <div class="row">
                                    <div class="col-9">
                                    Tickets Summary Chart (Service Center) <span class="badge badge-danger"></span>
                                    </div>
                                    <div class="col-3 text-end">
                                        <button type="button" class="text-white btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                            <i class='bx bx-filter'></i> Filter
                                        </button>
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
                                                        <th>Date</th>
                                                        <th>Total Tickets</th>
                                                        <th>Resolved Tickets</th>
                                                        <th>Escalated Tickets</th>
                                                        <th>Open Tickets</th>
                                                        <th>Closed Tickets</th>
                                                        <th>FCR (%)</th>
                                                        <th>Within Time (%)</th>
                                                        <th>Out of Time (%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($ticketStores as $date => $data)
                                                        <tr>
                                                            <td>{{ $date }}</td>
                                                            <td>{{ $data['total_tickets'] }}</td>
                                                            <td>{{ $data['resolved_tickets'] }}</td>
                                                            <td>{{ $data['escalated_tickets'] }}</td>
                                                            <td>{{ $data['open_or_inprogress_or_escalated'] }}</td>
                                                            <td>{{ $data['closed_escalated'] }}</td>
                                                            <td>{{ $data['per_fcr'] }}%</td>
                                                            <td>{{ $data['per_withintime'] }}%</td>
                                                            <td>{{ $data['per_outoftime'] }}%</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Modal -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="filterModalLabel">Filter Options</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="GET" action="">
                                            <div class="row">
                                                <div class="col">
                                                    <label for="start_date" class="form-label">Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" value="{{ old('start_date', request('start_date')) }}" required>
                                                </div>
                                                <div class="col">
                                                    <label for="end_date" class="form-label">End Date</label>
                                                    <input type="date" class="form-control" name="end_date" value="{{ old('end_date', request('end_date')) }}" required>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col">
                                                    <label for="work_location" class="form-label">Work Place</label>
                                                    <select class="form-control" name="work_location">
                                                        <option value="" {{ request('work_location') == '' ? 'selected' : '' }}>All</option>
                                                        @foreach($work as $users)
                                                            <option value="{{ $users->work_location }}" {{ request('work_location') == $users->work_location ? 'selected' : '' }}>
                                                                {{ $users->work_location }}
                                                            </option>
                                                        @endforeach
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
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        <!-- JavaScript -->
        @include('dashboard.script')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <script>
            function exportTableToExcel(tableID, filename = ''){
    var table = document.getElementById(tableID);
    var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    return XLSX.writeFile(wb, filename || ('ExportedData.xlsx'));
}
            // Search functionality for the table
            document.getElementById('search-focus').addEventListener('input', function() {
                const searchText = this.value.toLowerCase();
                const rows = document.querySelectorAll('#my-table tbody tr');
                rows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = rowText.includes(searchText) ? '' : 'none';
                });
            });
        </script>
    </x-app-layout>
</body>
</html>
