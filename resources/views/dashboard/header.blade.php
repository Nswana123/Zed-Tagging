<meta name="csrf-token" content="{{ csrf_token() }}">
<nav class="main-navigation">
  <div class="sidebar-button">
    <i class="bx bx-menu sidebarBtn"></i>
  </div>
  <div class="" id="clock">...</div>

        <!-- User Profile Dropdown -->
        <div class="dropdown profile-details ms-3">
            <a href="#" class="nav-link dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-user"></i>
                {{ Auth::user()->fname }} {{ Auth::user()->lname }}
            </a>
            
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                @php
                    $user = Auth::user();
                    $user_group = $user->user_group;
                @endphp

                <!-- Super Admin Links -->
                @if($user_group && $user_group->group_name === 'super admin')
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bx bx-user"></i> User Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('settings.user') }}"><i class="bx bx-user-plus"></i> Manage Users</a></li>
                    <li><a class="dropdown-item" href="{{ route('settings.usergroup') }}"><i class="bx bx-group"></i> User Groups</a></li>
                    <li><a class="dropdown-item" href="{{ route('settings.userRole') }}"><i class="bx bxs-user-account"></i> Permissions</a></li>
                    <li><a class="dropdown-item" href="{{ route('settings.systemSettings') }}"><i class="bx bxs-cog"></i> System Settings</a></li>
                @else
                    <!-- Non-Super Admin Links -->
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bx bx-user"></i> User Profile</a></li>
                @endif

                <li><hr class="dropdown-divider"></li>

                <!-- Log Out Link (Visible for all users) -->
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="bx bx-log-out"></i> Log Out
                        </a>
                    </form>
                </li>
            </ul>
        </div>
        <div id="notification-bar" style="position: fixed; top: 0; width: 100%; z-index: 1050;"></div>
</nav>

<!-- Auto-update Clock and Idle Timeout Script -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    window.Echo.private('escalation-group.' + groupId)
    .listen('.ticket.escalated', (data) => {
        const notificationBar = document.getElementById('notification-bar');
        const notification = document.createElement('div');
        notification.className = 'alert alert-info';
        notification.innerHTML = `
            <strong>New Ticket Escalated!</strong>
            Ticket ID: ${data.ticket.id} - ${data.ticket.title}
        `;
        notificationBar.appendChild(notification);
    });
    let idleTime = 0;

function resetIdleTime() {
    idleTime = 0;
}

window.onload = resetIdleTime;
document.onmousemove = resetIdleTime;
document.onkeypress = resetIdleTime;

// Increment idle time every minute
setInterval(function() {
    idleTime += 1;

    if (idleTime >= 30) { // 30 minutes of inactivity
        fetch('/check-session')
            .then(response => {
                if (response.status === 401) {
                    // Session expired, redirect to login
                    window.location.href = '/login';
                } else {
                    // Session active, log out
                    document.getElementById('logoutForm').submit();
                }
            })
            .catch(error => {
                console.error('Error checking session:', error);
            });
    }
}, 60000); // 1-minute interval
setInterval(function() {
        fetch('/refresh-csrf')
            .then(response => response.json())
            .then(data => {
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrfToken);
                document.querySelector('input[name="_token"]').value = data.csrfToken;
            })
            .catch(error => {
                console.error('Error refreshing CSRF token:', error);
            });
    }, 600000); // 10 minutes


    function updateClock() {
        const clockElement = document.getElementById('clock');
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        clockElement.innerHTML = `${hours}:${minutes}:${seconds}`;
    }

    setInterval(updateClock, 1000);
    updateClock();
</script>

<!-- Hidden Logout Form -->
<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
