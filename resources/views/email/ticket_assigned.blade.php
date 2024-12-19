<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('dashboard.style')
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/dashboard/style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <title>Ticket Assignment</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h4>You have been assigned a new ticket</h4>
    <p>Here are the details:</p>
    <table class="mt-3">
        <tr>
            <th>Ticket ID</th>
            <th>Opened At</th>
            <th>Site Name</th>
            <th>Fault Severity</th>
            <th>Time to Resolve</th>
            <th>Priority</th>
            <th>Fault Occurrence</th>
            <th>Fault Type</th>
            <th>Fault Description</th>
        </tr>
        <tr>
            <td><a href="{{ route('noc_tickets.showNocTickets', $ticket->id) }}">{{ $ticket->case_id }}</a></td>
            <td>{{ $ticket->created_at }}</td>
            <td>{{ $ticket->site_name }}</td>
            <td>{{ $ticket->fault_severity }}</td>
            <td>{{ $ticket->faulty_type->ttr_in_hour }} hrs</td>
            <td>
                @if($ticket->faulty_type)
                    @if($ticket->faulty_type->priority == 'severe')
                        Severe
                    @elseif($ticket->faulty_type->priority == 'high')
                        High
                    @elseif($ticket->faulty_type->priority == 'medium')
                        Medium
                    @else
                        Low
                    @endif
                @else
                    N/A
                @endif
            </td>
            <td>{{ $ticket->fault_occurrence_time }}</td>
            <td>{{ $ticket->faulty_type->fault_type }}</td>
            <td>{{$ticket->fault_description}}</td>
        </tr>
    </table>
</body>
</html>
