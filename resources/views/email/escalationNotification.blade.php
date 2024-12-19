<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Ticket Escalation</title>
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
<p>Hello {{ $user->fname }},</p>
    <p>You have a new ticket escalated to your group:</p>

    <table class="table" >
        <thead>
            <tr>
                <th>#</th>
                <th>Ticket ID</th>
                <th>Logged Time</th>
                <th>MSISDN</th>
                <th>Full Name</th>
                <th>SL (hrs)</th>
                <th>Priority</th>
                <th>Issue Category</th>
                <th>Issue Detail</th>
                <th>Issue Description</th>
                <th>Ticket Status</th>
                <th>Escalator</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><a href="{{ route('ticketing.showEscalatedTickets', $ticket->id) }}">{{ $ticket->case_id }}</a></td>
                <td>{{ $ticket->created_at }}</td>
                <td>{{ $ticket->msisdn }}</td>
                <td>{{ $ticket->title }} {{ $ticket->fname }} {{ $ticket->lname }}</td>
                <td>{{ $ticket->ticket_category->ticket_sla->ttr_in_hour ?? 'N/A' }} hrs</td>
                <td>
                    @if ($ticket->ticket_category && $ticket->ticket_category->ticket_sla)
                        {{ ucfirst($ticket->ticket_category->ticket_sla->priority) }}
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $ticket->ticket_category->category_name ?? 'N/A' }}</td>
                <td>{{ $ticket->ticket_category->category_detail ?? 'N/A' }}</td>
                <td>{{ $ticket->issue_description }}</td>
                <td>{{ $ticket->ticket_status }}</td>
                <td>
                    {{ optional($ticket->user_tickets->first()->claimer)->fname }} {{ optional($ticket->user_tickets->first()->claimer)->lname }}
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>