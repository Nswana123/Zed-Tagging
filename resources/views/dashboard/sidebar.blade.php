<div class="sidebar">
      <div class="logo-details">
        <i class="bx bx-user"></i>
        @php
                        $user = Auth::user();
                        $user_group = $user->user_group;
                    @endphp
        <span class="logo_name">{{ $user->user_group->group_name }}</span>
      </div>
      <ul class="side-nav">
    @if($permissions->contains('name', 'Dashboard'))
    <li>
        <a href="{{route('dashboard')}}" onclick="setActiveClass(this)" title ="Dashboard">
            <i class="bx bx-grid-alt"></i>
            <span class="links_name">Dashboard</span>
        </a>
    </li>
    @endif

    @if($permissions->contains('name', 'Add Tags'))
    <li >
    <a href="{{route('Ticketing')}}" onclick="setActiveClass(this)" title ="New Ticket">
        <i class='bx bx-add-to-queue'></i>
        <span class="links_name">New Ticket</span>
    </a>
</li>
    @endif
 
    @if($permissions->contains('name', 'sales'))
    <li>
        <a href="{{route('sales.Sales')}}" onclick="setActiveClass(this)" title ="Add Sales">
        <i class='bx bxs-cart-add'></i>
            <span class="links_name">Add Sales</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'View Open Tickets'))
    <li>
        <a href="{{route('open-tickets')}}" onclick="setActiveClass(this)" title ="Open Tickets">
            <i class='bx bxs-spreadsheet'></i>
            <span class="links_name">Open Tickets</span>
        </a>
    </li>
    @endif

    @if($permissions->contains('name', 'View Closed Tickets'))
    <li>
        <a href="{{route('Closed-tickets')}}" onclick="setActiveClass(this)" title ="Closed Tickets">
            <i class='bx bx-window-close'></i>
            <span class="links_name">Closed Tickets</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'All Tickets'))
    <li>
    <a href="{{route('all-tickets')}}" onclick="setActiveClass(this)" title ="All Tickets">
        <i class='bx bxs-inbox'></i>
        <span class="links_name" >All Tickets</span>
    </a>
</li>
    @endif
    @if($permissions->contains('name', 'Inbox Tickets'))
    <li style="position: relative;">
    <a href="{{ route('inbox') }}" onclick="setActiveClass(this)" style="position: relative;" title ="Inbox">
        <!-- Notification badge on top of the icon -->
     
       <i class='bx bxl-messenger'>   <span class="badge badge-danger" style="position: absolute; top: -5px;">{{ $inboxTickets }}</span></i>
        <span class="links_name">Inbox</span>
    </a>
</li>
    @endif
    @if($permissions->contains('name', 'Messages'))
    <li>
    <a href="{{route('Messages')}}" onclick="setActiveClass(this)" title ="Messages">
    <i class='bx bxl-messenger'></i>
        <span class="links_name" >Messages</span>       
    </a>
</li>
    @endif
    @if($permissions->contains('name', 'Refunds'))
    <li>
    <a href="{{route('refundList')}}" onclick="setActiveClass(this)" title ="All Refunds">
        <i class='bx bxs-inbox'></i>
        <span class="links_name" >All Refunds</span>
    </a>
</li>
@endif
    @if($permissions->contains('name', 'Claimed Tickets'))
    <li>
        <a href="{{route('claimedTickets')}}" onclick="setActiveClass(this)" title ="Claimed Tickets">
            <i class="bx bx-coin-stack"></i>
            <span class="links_name">Claimed Tickets</span>
        </a>
    </li>
    @endif

    @if($permissions->contains('name', 'Unclaimed Tickets'))
    <li>
        <a href="{{route('unclaimedTickets')}}" onclick="setActiveClass(this)" title ="Unclaimed Tickets">
            <i class='bx bx-add-to-queue' ></i>
            <span class="links_name">Unclaimed Tickets</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Escalated Tickets'))
    <li>
        <a href="{{route('EscalatedTickets')}}" onclick="setActiveClass(this)" title ="Escalated Tickets">
            <i class="bx bx-coin-stack"></i>
            <span class="links_name">Escalated Tickets</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Resolved Tickets'))
    <li>
        <a href="{{route('ResolvedTickets')}}" onclick="setActiveClass(this)" title ="Resolved Tickets">
            <i class="bx bx-coin-stack"></i>
            <span class="links_name">Resolved Tickets</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'complaint category'))
    <li class="dropdown">
    <a href="#" class="dropdown-toggle" onclick="setActiveClass(this)" title ="Category & Messages">
        <i class='bx bx-add-to-queue'></i>
        <span class="links_name">Category & Messages</span>
    </a>
    <ul class="dropdown-menu">
        <li>
            
            <a href="{{route('category.ticket-sla')}}">Complaints Sla</a>
        </li>
        <li>
            <a href="{{route('category.ticket-cat')}}">Complaints Category</a>
        </li>
        <li>
            <a href="{{route('category.ticket-sms')}}">Add Messages</a>
        </li>
        <li>
            <a href="{{route('sales.products')}}">Add Products</a>
        </li>
    </ul>
</li>
@endif
@if($permissions->contains('name', 'Locations & Devices'))
    <li class="dropdown">
    <a href="#" class="dropdown-toggle" onclick="setActiveClass(this)" title ="Locations & Devices">
    <i class='bx bx-location-plus'></i>
        <span class="links_name">Locations & Devices</span>
    </a>
    <ul class="dropdown-menu">
        <li>
            
            <a href="{{route('devices.devices')}}">Add Devices</a>
        </li>
        <li>
            <a href="{{route('devices.location')}}">Add Locations</a>
        </li>
       
    </ul>
</li>
@endif
@if($permissions->contains('name', 'ticket quality'))
    <li>
        <a href="{{route('ticket-quality')}}" onclick="setActiveClass(this)" title ="Ticket Quality">
            <i class='bx bxs-report'></i>
            <span class="links_name">Ticket Quality</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'serivce reports'))
    <li>
        <a href="{{route('service_records')}}" onclick="setActiveClass(this)" title ="Service Management">
            <i class='bx bxs-report'></i>
            <span class="links_name">Service Management</span>
        </a>
    </li>
    @endif
   
    @if($permissions->contains('name', 'agent reports'))
    <li>
        <a href="{{route('agent-records')}}" onclick="setActiveClass(this)" title ="Agents Report">
            <i class='bx bxs-report'></i>
            <span class="links_name">Agents Report</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Sales summary'))
    <li>
        <a href="{{route('Sales-sammury')}}" onclick="setActiveClass(this)" title ="Sales Summary">
            <i class='bx bxs-report'></i>
            <span class="links_name">Sales Summary</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'sales agent reports'))
    <li>
        <a href="{{route('Sales_agent-records')}}" onclick="setActiveClass(this)" title ="Sales Agents Report">
            <i class='bx bxs-report'></i>
            <span class="links_name">Sales Agents Report</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Sales reports'))
    <li>
        <a href="{{route('sales-reports')}}" onclick="setActiveClass(this)" title ="Sales Report">
            <i class='bx bxs-report'></i>
            <span class="links_name">Sales Report</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'reports'))
    <li>
        <a href="{{route('reports')}}" onclick="setActiveClass(this)" title ="Generate Report">
            <i class='bx bxs-report'></i>
            <span class="links_name">Generate Report</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Noc Ticketing'))
    <li>
        <a href="{{route('ticket')}}" onclick="setActiveClass(this)" title ="Noc New Ticket">
        <i class='bx bxs-add-to-queue'></i>
            <span class="links_name">Noc New Ticket</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Unassigned Ticket'))
    <li>
        <a href="{{route('NocOpenTickets')}}" onclick="setActiveClass(this)" title ="Unassigned Ticket">
        <i class='bx bx-list-ul' ></i>
            <span class="links_name">Unassigned Ticket</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Assigned Tickets'))
    <li>
        <a href="{{route('NocAssignedTickets')}}" onclick="setActiveClass(this)" title ="Assigned Tickets">
        <i class='bx bx-list-check'></i>
            <span class="links_name">Assigned Tickets</span>
        </a>
    </li>
    @endif
 
    @if($permissions->contains('name', 'Noc Escalated Ticket'))
    <li>
        <a href="{{route('NocEscalatedTickets')}}" onclick="setActiveClass(this)" title ="Noc Escalated Ticket">
        <i class='bx bx-list-ul' ></i>
            <span class="links_name">Noc Escalated Ticket</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Noc Closed Ticket'))
    <li>
        <a href="{{route('NocClosedTickets')}}" onclick="setActiveClass(this)" title ="Noc Closed Ticket">
        <i class='bx bx-window-close'></i>
            <span class="links_name">Noc Closed Ticket</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Noc All Ticket'))
    <li>
        <a href="{{route('NocAllTickets')}}" onclick="setActiveClass(this)" title ="Noc All Ticket">
        <i class='bx bx-list-ul' ></i>
            <span class="links_name">Noc All Ticket</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Faulty Type'))
    <li>
        <a href="{{route('noc_tickets.sitefaults')}}" onclick="setActiveClass(this)" title ="Add Faults">
        <i class='bx bx-plus'></i>
            <span class="links_name">Add Faults</span>
        </a>
    </li>
    @endif
    @if($permissions->contains('name', 'Noc Main Report'))
    <li>
        <a href="{{route('NocReport')}}" onclick="setActiveClass(this)" title ="Noc Main Report">
            <i class='bx bxs-report'></i>
            <span class="links_name">Noc Main Report</span>
        </a>
    </li>
    @endif
   
</ul>

    </div>
