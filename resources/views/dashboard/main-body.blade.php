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
@endif    @php
                    $user = Auth::user();
                    $user_group = $user->user_group;
                @endphp

                <!-- Super Admin Links -->
                @if($user_group && in_array($user_group->group_name, ['Network Team','super admin']))
                <div class="row " style="margin-top:-30px">
        <div class="col">
            <div class="row">
                <div class="col-lg p-3">
                    <div class="card shadow-lg bg-white" style="border-radius:10px;overflow:hidden;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="fs-6 fw-bold">All Noc Tickets</h5>
                                </div>
                                <div class="col-4 mt-4">
                                 <i class='bx bxs-briefcase-alt-2 cart p-2'></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="number">{{ $totalNocTickets }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg p-3">
                    <div class="card shadow-lg bg-white" style="border-radius:10px;overflow:hidden;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="fs-6 fw-bold">Unassigned Tickets</h5>
                                </div>
                                <div class="col-4 mt-4">
                                <i class='bx bx-folder-open cart p-2'></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="number">{{ $openNocTickets }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="row">
                <div class="col-lg p-3">
                    <div class="card shadow-lg bg-white" style="border-radius:10px;overflow:hidden;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="fs-6 fw-bold">Assigned Tickets</h5>
                                </div>
                                <div class="col-4 mt-4">
                                  <i class='bx bxs-wallet cart p-2'></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="number">{{ $inprogrssNocTickets }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg p-3">
                    <div class="card shadow-lg bg-white" style="border-radius:10px;overflow:hidden;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="fs-6 fw-bold">Closed | Escalated Tickkets</h5>
                                </div>
                                <div class="col-4 mt-4">
                                 <i class='bx bx-mail-send cart four p-2'></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="number">{{ $closedNocTickets}} | {{$escalatedNocTickets}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Top 10 Faulty Sites-->
   <div class="row">
        <div class="col-lg-6 p-3">
            <div class="card shadow-lg bg-white">
                <div class="card-body">
                    <h3>Top 10 Serviced Sites</h3>
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col-6"></div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultsitesData('day')">D</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultsitesData('week')">W</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultsitesData('month')">M</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultsitesData('quarter')">Q</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultsitesData('year')">Y</button>
                            </div>
                        </div>
                        <div class="row">
                            <div style="width: 100%; margin: 0;">
                                <canvas id="faultySitesData"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 10 Faulty types -->
        <div class="col-lg-6 p-3">
            <div class="card shadow-lg bg-white">
                <div class="card-body">
                    <h3>Top 10 Sites Faulty Type</h3>
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col-6"></div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultytypeData('day')">D</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultytypeData('week')">W</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultytypeData('month')">M</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultytypeData('quarter')">Q</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchFaultytypeData('year')">Y</button>
                            </div>
                        </div>
                        <div class="row">
                            <div style="width: 100%; margin: 0;">
                                <canvas id="faultTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



                @else
<div class="row " style="margin-top:-30px">
        <div class="col">
            <div class="row">
                <div class="col-lg p-3">
                    <div class="card shadow-lg bg-white" style="border-radius:10px;overflow:hidden;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="fs-6 fw-bold">All | FCR | Non-FCR Tickets</h5>
                                </div>
                                <div class="col-4 mt-4">
                                 <i class='bx bxs-briefcase-alt-2 cart p-2'></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="number">{{ $totalTickets }} | {{$FCRTickets}} | {{$NonFCRTickets}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg p-3">
                    <div class="card shadow-lg bg-white" style="border-radius:10px;overflow:hidden;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="fs-6 fw-bold">Pending | Inprogress Tickets</h5>
                                </div>
                                <div class="col-4 mt-4">
                                <i class='bx bx-folder-open cart p-2'></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="number">{{ $openTickets }} | {{$inprogrssTickets}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="row">
                <div class="col-lg p-3">
                    <div class="card shadow-lg bg-white" style="border-radius:10px;overflow:hidden;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="fs-6 fw-bold">Closed | Escalated Tickkets</h5>
                                </div>
                                <div class="col-4 mt-4">
                                  <i class='bx bxs-wallet cart p-2'></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="number">{{ $closedTickets }} | {{ $escalatedTickets }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg p-3">
                    <div class="card shadow-lg bg-white" style="border-radius:10px;overflow:hidden;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="fs-6 fw-bold">Claimed | Unclaimed Tickets</h5>
                                </div>
                                <div class="col-4 mt-4">
                                 <i class='bx bx-mail-send cart four p-2'></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="number">{{ $claimedTickets}} | {{$unclaimedTickets}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Top 10 User Devices Chart -->
   <div class="row">
        <div class="col-lg-6 p-3">
            <div class="card shadow-lg bg-white">
                <div class="card-body">
                    <h3>Top 10 User Devices</h3>
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col-6"></div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchDeviceData('day')">D</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchDeviceData('week')">W</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchDeviceData('month')">M</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchDeviceData('quarter')">Q</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchDeviceData('year')">Y</button>
                            </div>
                        </div>
                        <div class="row">
                            <div style="width: 100%; margin: 0;">
                                <canvas id="customerDevicesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 10 Complaint Types Chart -->
        <div class="col-lg-6 p-3">
            <div class="card shadow-lg bg-white">
                <div class="card-body">
                    <h3>Top 10 Complaint Type</h3>
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col-6"></div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchData('day')">D</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchData('week')">W</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchData('month')">M</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchData('quarter')">Q</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchData('year')">Y</button>
                            </div>
                        </div>
                        <div class="row">
                            <div style="width: 100%; margin: 0;">
                                <canvas id="issueDetailsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       <!-- Top 10 Affected Areas -->
<div class="col-lg-6 p-3">
    <div class="card shadow-lg bg-white">
        <div class="card-body">
            <h3>Top 10 Affected Areas</h3>
            <div class="container">
                <div class="row mb-3">
                    <div class="col-6"></div>
                    <div class="col">
                        <button class="btn btn-primary" onclick="fetchLocationData('day')">D</button>
                    </div>
                    <div class="col">
                        <button class="btn btn-primary" onclick="fetchLocationData('week')">W</button>
                    </div>
                    <div class="col">
                        <button class="btn btn-primary" onclick="fetchLocationData('month')">M</button>
                    </div>
                    <div class="col">
                        <button class="btn btn-primary" onclick="fetchLocationData('quarter')">Q</button>
                    </div>
                    <div class="col">
                        <button class="btn btn-primary" onclick="fetchLocationData('year')">Y</button>
                    </div>
                </div>
                <div class="row">
                    <div style="width: 100%; margin: 0;">
                        <canvas id="physicalAddressChart"></canvas> <!-- Chart Canvas -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


        <!-- Top 10 Root Causes -->
        <div class="col-lg-6 p-3">
            <div class="card shadow-lg bg-white">
                <div class="card-body">
                    <h3>Top 10 Root Cause</h3>
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col-6"></div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchRouteCauseData('day')">D</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchRouteCauseData('week')">W</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchRouteCauseData('month')">M</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchRouteCauseData('quarter')">Q</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-primary" onclick="fetchRouteCauseData('year')">Y</button>
                            </div>
                        </div>
                        <div class="row">
                            <div style="width: 100%; margin: 0;">
                                <canvas id="routeCauseChart"></canvas>
                            </div>
                        </div>
</div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endif