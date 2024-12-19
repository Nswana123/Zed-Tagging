<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Reminder</title>
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
<h4>Ticket Remainder for {{ $groupName }} - {{ $date }}</h4>
    <p>Total tickets in progress {{ $ticketCount }}</p>
    <p>Here are the tickets:</p>
    <table>
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Logged Time</th>
                <th>MSISDN</th>
                <th>Full Name</th>
                <th>Issue Category</th>
                <th>Issue Detail</th>
                <th>Ticket Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
<td><a href="{{ route('ticketing.showClaimedTcikets', $ticket->id) }}">{{ $ticket->case_id }}</a></td>
                <td>{{ $ticket->created_at }}</td>
                <td>{{ $ticket->msisdn }}</td>
                <td>{{ $ticket->title }} {{ $ticket->fname }} {{ $ticket->lname }}</td>
                <td>{{ $ticket->ticket_category->category_name ?? 'N/A' }}</td>
                <td>{{ $ticket->ticket_category->category_detail ?? 'N/A' }}</td>
                <td>{{ $ticket->ticket_status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p>Please follow up on these tickets as soon as possible.</p>
</body>
</html>
