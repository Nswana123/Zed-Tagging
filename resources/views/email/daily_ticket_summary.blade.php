<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Ticket Summary</title>
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
    <h4>Daily Ticket Summary for {{ $groupName }} - {{ $date }}</h4>
    <p>Total tickets out of time for {{ $groupName }}: {{ $ticketCount }}</p>

    <p>Here are the tickets that were closed today and are out of time:</p>
    <table class="mt-3">
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Opened At</th>
                <th>Closed At</th>
                <th>Time Taken (hrs)</th>
                <th>Ticket Age</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
                <tr>
                    <td>
                        <a href="{{ route('ticketing.showallTicket', $ticket->id) }}" target="_blank">
                            {{ $ticket->case_id }}
                        </a>
                    </td>
                    <td>{{ $ticket->created_at }}</td>
                    <td>{{ $ticket->closed_date }}</td>
                    <td>{{ $ticket->time_taken }}</td>
                    <td>{{ $ticket->ticket_age }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
