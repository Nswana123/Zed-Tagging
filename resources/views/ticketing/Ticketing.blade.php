<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Support | CRM</title>

    @include('dashboard.style')
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/dashboard/style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa1z6uVRgb2db4BucAd5UzOUlmkn2e6A2ZgFgfkUtgXmW6oCdKw5D9Mx22i" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />


    <style>
        /* Add custom CSS here */
        .tab-titles{
    display: flex;
    margin: 20px 0 10px;
    justify-content: center;
    
}
.tab-link{
    margin-right: 50px;
    font-size: 18px;
    font-weight: 500;
    cursor: pointer;
    position: relative;
}
.tab-link::after{
    content: '';
    width: 0;
    height: 3px;
    background: #0A2558 !important;
    position: absolute;
    left: 0;
    bottom: -8px;
    transition: 0.3s;
    opaprovince: 0.8;
}
.tab-link.active-link::after{
    width: 50%;
}
.tab-contents ul li{
    list-style: none;
    margin: 10px 0;
}
.tab-contents ul li span{
    color: #ff004f !important;
    font-size: 14px;
    opaprovince: 0.8;

}
.tab-contents {
    background:#fff;
    display: none;
    justify-content: center;
}
.tab-contents.active-tab{
    display: block;
}
.other-skils p{
    font-size: 8px;
    margin-bottom: -10px;
}
.content{
    margin-top: -20px;
}
.hidden {
            display: none;
        }
        .toast-container {
            position: fixed;
            top: 60px;
            right: 60px;
            width:1000px;
           
        }
    </style>
</head>
<body>


    @include('dashboard.sidebar')

    <div class="home-section">
        @include('dashboard.header')

        <div class="home-content p-3">

            <!-- Error and Success Messages -->
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

            <!-- Ticket Creation Form -->
            <div class="row">
                <div class="col">
                    <div class="card bg-body shadow-sm" >
                        <div class="card-header p-3 text-white" style="background-color: #0A2558;">
                            
                            <div class="row">
                                <div class="col-lg">
                                Create New Ticket
                                </div>
                                <div class="col-lg">
                                <form action="{{ route('Ticketing') }}" method="GET">
            @csrf
            <div class="input-group">
            <input type="text" class="form-control" id="msisd" name="msisd" maxlength="10" placeholder="Search Profile By MSISDN" autocomplete = "off">
                <button type="submit" id="search-btn" class="btn btn-primary"><i class='bx bx-search'></i></button>
            </div>
               
            
        </form>

                        <!-- Display errors if any -->
                        @if ($errors->any())
                            <div class="alert alert-danger mt-1">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                      @else
                            <ul class="list-group mt-1">
                                @foreach ($tickets as $ticket)
                                    <li class="list-group-item">
                                        <a href="{{ route('ticketing.customerProfile', ['msisdn' => $ticket->msisdn]) }}">{{ $ticket->msisdn }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
    </div>
    </div>
                        <div class="card-body">
                            <form action="{{ route('create-ticket') }}" method="post" enctype="multipart/form-data" autocomplete="off">
                                @csrf

                                <!-- Customer's Personal Information -->
                                <fieldset class="border p-4 mb-4">
                                    <legend class="w-auto">Customer's Personal Information</legend>

                                    <div class="row">
    <div class="col-lg-6 mb-3">
        <label for="msisdn">MSISDN</label>
        <input type="text" class="form-control bg-body shadow-sm border-success" name="msisdn" id="msisdn" value="{{ old('msisdn') }}" autocomplete="off">
        @error('msisdn')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-lg-6 mb-3">
        <label for="primary_no">Alternative Number</label>
        <input type="text" class="form-control bg-body shadow-sm" name="primary_no" id="primary_no" value="{{ old('primary_no') }}" autocomplete="off">
        @error('primary_no')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="title">Title</label>
    <select class="form-control bg-body shadow-sm" name="title" id="title">
        <option value="">Select Customer's Title</option>
        <option value="Mr." {{ old('title') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
        <option value="Mrs." {{ old('title') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
        <option value="Ms." {{ old('title') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
        <option value="Dr." {{ old('title') == 'Dr.' ? 'selected' : '' }}>Dr.</option>
        <option value="Prof." {{ old('title') == 'Prof.' ? 'selected' : '' }}>Prof.</option>
    </select>
    @error('title')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="fname">First Name</label>
        <input type="text" class="form-control bg-body shadow-sm" name="fname" id="fname" value="{{ old('fname') }}" autocomplete="off">
        @error('fname')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-lg-6 mb-3">
        <label for="lname">Last Name</label>
        <input type="text" class="form-control bg-body shadow-sm" name="lname" id="lname" value="{{ old('lname') }}" autocomplete="off">
        @error('lname')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

</div>

                                </fieldset>

                                <!-- Customer's Issue Details -->
                                <fieldset class="border p-4 mb-4">
                                    <legend class="w-auto">Customer's Issue Details</legend>

                                    <div class="mb-3">
    <label for="complaintCategory">Complaint Category*</label>
    <select class="form-control bg-body shadow-sm" name="category_name" id="complaintCategory">
        <option value="" selected>Select Complaint Category</option>
        @foreach($ticket_cat as $cat)
            <option value="{{ $cat->category_name }}" {{ old('category_name') == $cat->category_name ? 'selected' : '' }}>{{ $cat->category_name }}</option>
        @endforeach
    </select>
    @error('category_name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="issue_type">Issue Type</label>
        <select class="form-control bg-body shadow-sm" name="category_type" id="issue_type">
            <option value="" selected>Select Issue Type</option>
            @foreach($ticket_cate as $cat)
                <option value="{{ $cat->category_type }}" {{ old('category_type') == $cat->category_type ? 'selected' : '' }}>{{ $cat->category_type }}</option>
            @endforeach
        </select>
        @error('category_type')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-lg-6 mb-3">
        <label for="issueDetail">Complaint Detail</label>
        <select class="form-control bg-body shadow-sm" name="cat_id" id="issueDetail">
            <option value="">Select An Issue</option>
        </select>
        @error('cat_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>



                                   <div class="row">
                                    <div class="col-lg">
<div class="row">
    <div class="col-lg">
                                        <label for="issue_description">Issue Description</label>
                                        <textarea class="form-control bg-body shadow-sm" name="issue_description" rows="3" id="issue_description" rows="2">{{ old('issue_description') }}</textarea>
                                        <small id="descriptionHelp" class="form-text text-muted"></small>
                                        @error('issue_description')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg">
                                        <div class="row">
                                        <div class="col-lg">
    <label>Preferred Contact Method</label>
    <select class="form-control bg-body shadow-sm" name="method_of_contact" id="method_of_contact" onchange="toggleContactFields()">
        <option value="">Select the preferred Contact Method</option>
        <option value="phone call" {{ old('method_of_contact') == 'phone call' ? 'selected' : '' }}>Phone Call</option>
        <option value="text" {{ old('method_of_contact') == 'text' ? 'selected' : '' }}>Text</option>
        <option value="email" {{ old('method_of_contact') == 'email' ? 'selected' : '' }}>Email</option>
        <option value="whatsapp" {{ old('method_of_contact') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
        <option value="facebook" {{ old('method_of_contact') == 'facebook' ? 'selected' : '' }}>Facebook</option>
    </select>

    <small id="method_of_contact" class="form-text text-muted"></small>
    @error('method_of_contact')
        <span class="text-danger">{{ $message }}</span>
    @enderror
    
    <div class="row" id="contactFieldContainer" style="display: none;">
        <div class="col-lg">
            <label>Contact</label>
            <input type="text" class="form-control bg-body shadow-sm" name="contact" id="contactField" placeholder="Enter contact detail" value="{{ old('contact') }}">
            @error('contact')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

                    <div class="col-lg">
                    <label>Product</label>
                                    <select name="product_id" class="form-control bg-body shadow-sm" id="product" >
                                            <option>Select Bought Product</option>
                                            @foreach($products as $cat)
                                                    <option value="{{ $cat->id }}" {{ old('product_id') == $cat->id ? 'selected' : '' }}>
                                                        {{ $cat->product }}
                                                    </option>
                                                @endforeach
    </select>
    </div>
                                        </div>
                                    </div>
  
</div>
                                    </div>
                                   </div>

                                    <div class="row mt-3">
                                        <div class="col-lg-6 mb-3">
                                            <label for="duration_of_experience">Duration Of Experience</label>
                                            <input type="text" class="form-control bg-body shadow-sm" name="duration_of_experience" id="duration_of_experience" value="{{ old('duration_of_experience') }}" autocomplete="off">
                                            <small id="doeHelp" class="form-text text-muted"></small>
                                            @error('duration_of_experience')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6 mb-3">
                                            <label for="interactionStatus">Continuous or Intermittent</label>
                                            <select class="form-control bg-body shadow-sm" name="issue_status" id="issuestatus">
                                                <option value="">Is the Issue Continuous or Intermittent?</option>
                                                <option value="Continuous" {{ old('issue_status') == 'Continuous' ? 'selected' : '' }}>Continuous</option>
                                                <option value="Intermittent" {{ old('issue_status') == 'Intermittent' ? 'selected' : '' }}>Intermittent</option>
                                            </select>
                                            <small id="issueStatusHelp" class="form-text text-muted"></small>
                                            @error('issue_status')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
    <div class="col-lg-6 mb-3">
        <label for="deviceInput">Select Customer Device</label>
        <input list="devices" name="device_name" id="deviceInput" class="form-control" placeholder="Select Customer Device" onchange="checkNewDevice()" autocomplete="off">
        <input type="hidden" name="device_id" id="device_id" autocomplete="off"> <!-- Hidden field to store device_id -->

        <datalist id="devices">
            @foreach($devices as $device)
                <option data-id="{{ $device->id }}" value="{{ $device->brand }} {{ $device->model }}">
            @endforeach
            <option value="new_device">Add New Device</option> <!-- Trigger to show new device fields -->
        </datalist>

        <small id="deviceHelp" class="form-text text-muted"></small>
        @error('device_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror

        <!-- New Device Fields Section -->
        <div id="new_device_fields" style="display: none;">
            <div class="row mb-3 mt-3">
                <div class="col-lg">
                    <label for="new_brand">Device Brand</label>
                    <input type="text" class="form-control bg-body shadow-sm" name="brand" id="brand" value="{{ old('brand') }}">
                    @error('brand')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-lg">
                    <label for="model">Model</label>
                    <input type="text" class="form-control bg-body shadow-sm" name="model" id="model" value="{{ old('model') }}">
                    @error('model')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-3">
        <label for="locationInput">Select Customer Location</label>
        <input list="locations" name="location_name" id="locationInput" class="form-control" placeholder="Select Customer Location" onchange="checkNewLocation()" autocomplete="off">
        <input type="hidden" name="location_id" id="location_id" autocomplete="off"> <!-- Hidden field to store location_id -->

        <datalist id="locations">
            <option value="" selected>Select Customer Location</option>
            @foreach($locations as $location)
                <option data-id="{{ $location->id }}" value="{{ $location->province }}, {{ $location->town }}, {{ $location->landmark }}"></option>
            @endforeach
            <option value="new_location">Add New Location</option> <!-- Trigger to show new location fields -->
        </datalist>

        <small id="locationHelp" class="form-text text-muted"></small>
        @error('location_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror

        <!-- New Location Fields Section -->
        <div id="new-location-fields" style="display: none;">
            <div class="row mb-3 mt-3">
                <div class="col-lg">
                    <label for="province">Province</label>
                    <select class="form-control" name="province" id="province">
                    <option value="Lusaka" {{ old('province') == 'Lusaka' ? 'selected' : '' }}>Lusaka</option>
                    <option value="Central" {{ old('province') == 'Central' ? 'selected' : '' }}>Central</option>
                    <option value="Copperbelt" {{ old('province') == 'Copperbelt' ? 'selected' : '' }}>Copperbelt</option>
                    <option value="Eastern" {{ old('province') == 'Eastern' ? 'selected' : '' }}>Eastern</option>
                    <option value="Luapula" {{ old('province') == 'Luapula' ? 'selected' : '' }}>Luapula</option>
                    <option value="Muchinga" {{ old('province') == 'Muchinga' ? 'selected' : '' }}>Muchinga</option>
                    <option value="Northern" {{ old('province') == 'Northern' ? 'selected' : '' }}>Northern</option>
                    <option value="North-Western" {{ old('province') == 'North-Western' ? 'selected' : '' }}>North Western</option>
                    <option value="Southern" {{ old('province') == 'Southern' ? 'selected' : '' }}>Southern</option>
                    <option value="Western" {{ old('province') == 'Western' ? 'selected' : '' }}>Western</option>

                        <!-- Add other provinces as needed -->
                    </select>
                    @error('province')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-lg">
                    <label for="town">Town</label>
                    <input type="text" class="form-control" name="town" id="town" value="{{ old('town') }}">
                    @error('town')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-lg">
                    <label for="landmark">Landmark</label>
                    <input type="text" class="form-control" name="landmark" id="landmark" value="{{ old('landmark') }}">
                    @error('landmark')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>


                                    <div class="row">
                                        <div class="col-lg">
                                            <div class="row">
                                            <div class="col-lg">
                        <label>Interaction Status</label>
                        <select class="form-control bg-body shadow-sm" name="interaction_status" id="interactionstatus">
                            <option value="">Select An Interaction Status</option>
                            <option value="Resolved" {{ old('interaction_status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="Escalated" {{ old('interaction_status') == 'Escalated' ? 'selected' : '' }}>Escalated</option>
                        </select>
                        <small id="interactionHelp" class="form-text text-muted"></small>
                        @error('interaction_status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-lg">
                    <label>Root Cause</label>
                                    <select name="root_cause" class="form-control bg-body shadow-sm" id="root">
                                    <option>Select problem Root Cause</option>
    <option value="Agent Error">Agent Error</option>
    <option value="Wrong Configuration">Wrong Configuration</option>
    <option value="Customer Error" >Customer Error</option>
    <option value="Device Error" >Device Error</option>
    <option value="Incompatible Device" >Incompatible Device</option>
    <option value="No Coverage" >No Coverage</option>
    <option value="Poor Coverage" >Poor Coverage</option>
    <option value="No Coverage (Fault)" >No Coverage (Fault)</option>
    <option value="System Error" >System Error</option>
    <option value="Product Misinformation">Product Misinformation</option>
    <option value="Depleted Bundle">Depleted Bundle</option>
    <option value="Wrong Bundle" >Wrong Bundle</option>
    <option value="Delayed Activation">Delayed Activation</option>
    <option value="Network Congestion" >Network Congestion</option>
    <option value="SIM Card Issues" >SIM Card Issues</option>
    <option value="Fraudulent Activity" >Fraudulent Activity</option>
    <option value="Billing Discrepancy" >Billing Discrepancy</option>
    <option value="Service Interruption" >Service Interruption</option>
    <option value="Technical Maintenance" >Technical Maintenance</option>
    <option value="Subscription Error" >Subscription Error</option>
    <option value="Overcharged" >Overcharged</option>
    <option value="Unauthorized Account Changes" >Unauthorized Account Changes</option>
    <option value="Porting Issues">Porting Issues</option>
    <option value="Roaming Issues" >Roaming Issues</option>
    <option value="Application Error" >Application Error</option>
    <option value="Miscommunication">Miscommunication</option>
    <option value="Expired Subscription" >Expired Subscription</option>
    <option value="Blocked Account" >Blocked Account</option>
    <option value="Unresponsive Customer Care">Unresponsive Customer Care</option>
    <option value="Data Throttling" >Data Throttling</option>
    <option value="Misleading Promotions" >Misleading Promotions</option>
    <option value="Hardware Malfunction" >Hardware Malfunction</option>
    <option value="Signal Interference" >Signal Interference</option>
    <option value="Third-Party Provider Issues" >Third-Party Provider Issues</option>
    <option value="Unnotified Plan Changes" >Unnotified Plan Changes</option>
    <option value="Unauthorized Access" >Unauthorized Access</option>
    <option value="User Authentication Issues" >User Authentication Issues</option>
    <option value="Expired SIM" >Expired SIM</option>
    <option value="Compatibility Updates Needed" >Compatibility Updates Needed</option>
    <option value="Call Drop Issues" >Call Drop Issues</option>
    <option value="Slow Internet Speed" >Slow Internet Speed</option>
    <option value="Data Usage Misreporting" >Data Usage Misreporting</option>
    <option value="Unreceived OTP" >Unreceived OTP</option>
    <option value="Duplicate Charges" >Duplicate Charges</option>
    <option value="Voice Quality Issues">Voice Quality Issues</option>
    <option value="Delayed Refunds" >Delayed Refunds</option>
    <option value="Invalid Recharge">Invalid Recharge</option>
    <option value="Missed Plan Benefits" >Missed Plan Benefits</option>
    <option value="Promotional Spam">Promotional Spam</option>
    <option value="Unauthorized SIM Swap" >Unauthorized SIM Swap</option>
    <option value="Unsupported Features" >Unsupported Features</option>
    <option value="Error in International Roaming" >Error in International Roaming</option>
    <option value="Downtime Notification Delay">Downtime Notification Delay</option>
    </select>
    <small id="rootHelp" class="form-text text-muted"></small>
                                    @error('route_cause')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                    </div>
                    
                                   
                                            </div>
                                            <div class="row">
                                            <div class="col-lg mt-3">
                                                <div class="row">
                                                    <div class="col-lg">
                                                    <label>Action Taken:</label>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Checked APN Settings" value="Checked APN Settings">
    <label class="form-check-label" for="Checked APN Settings">Checked APN Settings</label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Checked the AgentView" value="Checked the AgentView">
    <label class="form-check-label" for="Checked the AgentView">Checked the AgentView</label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Restarted Device" value="Restarted Device">
    <label class="form-check-label" for="Restarted Device">Restarted Device</label>
</div>
                                                    </div>
                                                    <div class="col-lg mt-3">
                                                    <div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Put on Flight Mode" value="Put on Flight Mode">
    <label class="form-check-label" for="Put on Flight Mode">Put on Flight Mode</label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="action_taken[]" id="Tried the sim in another device" value="Tried the sim in another device">
    <label class="form-check-label" for="Tried the sim in another device">Tried the sim in another device</label>
</div>
                                                    </div>
                                                </div>
              


                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg mt-3">
                                    <!-- Attachments Section (You can add fields for file uploads if needed) -->
                                    <fieldset class="border p-4 mb-4">
                                        <legend class="w-auto">Attachments</legend>

                                        <div class="mb-3">
                                            <label for="attachments">Attachments (Images)</label>
                                            <input type="file" class="form-control bg-body shadow-sm" name="attachments[]" id="attachments" accept="image/*" multiple onchange="previewMultipleImages(event)">
                                            <div id="previews" class="mt-2" style="display: flex; flex-wrap: wrap;"></div> <!-- Container for image previews -->
                                            @error('attachments')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </fieldset>
                                        </div>
                                    </div>

                                </fieldset>

                                <!-- Submit Button -->

                                 <div class="row m-3 mb-3">
                <div class="col-lg">
                    <button type="submit" class="btn btn-primary float-end">Create Ticket</button>
                </div>
            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

          
        </div>
    </div>
<!-- Toast Notification Structure -->
<div id="toast-container" style="position: fixed; top: 10px; right: 10px;"></div>


@include('dashboard.script')

<!-- You can include additional JavaScript/jQuery for dynamic form interactions -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoYz1zWbFOUT1a6DkZpvv9SOAzFcuGy4Lkfz1FO6DOMoarI" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
    $('.select2').select2({
        placeholder: "Select Customer Device",
        allowClear: true
    });
});
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Select Customer Location",
        allowClear: true
    });
});
  function toggleContactFields() {
    const methodOfContact = document.getElementById('method_of_contact').value;
    const contactFieldContainer = document.getElementById('contactFieldContainer');
    const contactField = document.getElementById('contactField');

    // Show the contact field only for email, whatsapp, or facebook
    if (methodOfContact === 'email' || methodOfContact === 'whatsapp' || methodOfContact === 'facebook') {
        contactFieldContainer.style.display = 'block';

        // Change placeholder based on selected method
        switch (methodOfContact) {
            case 'email':
                contactField.placeholder = 'Enter email address';
                break;
            case 'whatsapp':
                contactField.placeholder = 'Enter WhatsApp number';
                break;
            case 'facebook':
                contactField.placeholder = 'Enter Facebook username';
                break;
        }
    } else {
        // Hide the contact field if any other method is selected
        contactFieldContainer.style.display = 'none';
        contactField.value = ''; // Clear the input value if hidden
    }
}

function checkNewLocation() {
        const locationInput = document.getElementById('locationInput').value;
        const locationIdField = document.getElementById('location_id');
        const newLocationFields = document.getElementById('new-location-fields');

        if (locationInput === 'new_location') {
            newLocationFields.style.display = 'block';
            locationIdField.value = 'new_location'; // Indicate that a new location is being added
        } else {
            newLocationFields.style.display = 'none';

            // Set the selected location's ID in the hidden location_id field
            const options = document.getElementById('locations').options;
            for (let option of options) {
                if (option.value === locationInput) {
                    locationIdField.value = option.getAttribute('data-id');
                    break;
                }
            }
        }
    }   
    function checkNewDevice() {
    const deviceInput = document.getElementById('deviceInput').value;
    const deviceIdField = document.getElementById('device_id');
    const newDeviceFields = document.getElementById('new_device_fields');

    if (deviceInput === 'new_device') {
        newDeviceFields.style.display = 'block';
        deviceIdField.value = 'new_device';
    } else {
        newDeviceFields.style.display = 'none';

        // Find matching option in datalist and set device_id
        const options = document.getElementById('devices').options;
        for (let option of options) {
            if (option.value === deviceInput) {
                deviceIdField.value = option.getAttribute('data-id');
                break;
            }
        }
    }
}
    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$(document).ready(function() {
    $('#msisdn').on('input', function() {
        var msisdn = $(this).val();

        // Check if exactly 10 digits are typed
        if (msisdn.length === 10) {
            $.ajax({
                url: "{{ route('check.msisdn') }}",  // Ensure this route is defined in your Laravel routes file
                method: "GET",
                data: { msisdn: msisdn },
                success: function(response) {
                    if (response.status === 'found') {
                        // Populate the fields with the retrieved data
                        $('input[name="primary_no"]').val(response.data.primary_no);
                        $('select[name="title"]').val(response.data.title);
                        $('input[name="fname"]').val(response.data.fname);
                        $('input[name="lname"]').val(response.data.lname);

                        // Prepare the ticket info for the pop-up
                        var ticketsInfo = '';
                        $.each(response.tickets, function(index, ticket) {
                            // Check if ticket_resolutions exists and is an array
                            if (Array.isArray(ticket.ticket_resolutions)) {
                                var finalResolution = ticket.ticket_resolutions.find(resolution => resolution.closed === 'final');
                                var resolutionRemarks = finalResolution ? finalResolution.resolution_remarks : 'No Closing Remarks';

                                ticketsInfo += '<div class="ticket-info">' +
                                               '<span class="ticket-id-status">' +
                                               '<strong>Ticket ID:</strong> ' + ticket.case_id + ' | ' +
                                               '<strong>Status:</strong> ' + ticket.ticket_status + ' | ' +
                                               '<strong>Created At:</strong> ' + ticket.created_at + '</span><br>' +
                                               '<strong>Issue:</strong> ' + ticket.issue_description + '<br>' +
                                               '<strong>Resolution:</strong> ' + resolutionRemarks + '<hr>' +
                                               '</div>';
                            } else {
                                ticketsInfo += '<div class="ticket-info">' +
                                               '<span class="ticket-id-status">' +
                                               '<strong>Ticket ID:</strong> ' + ticket.case_id + ' | ' +
                                               '<strong>Status:</strong> ' + ticket.ticket_status + ' | ' +
                                               '<strong>Created At:</strong> ' + ticket.created_at + '</span><br>' +
                                               '<strong>Issue:</strong> ' + ticket.issue_description + '<br>' +
                                               '<strong>Resolution:</strong> No Resolutions Available<hr>' +
                                               '</div>';
                            }
                        });

                        // Show the pop-up with ticket information
                        Swal.fire({
                            toast: false,
                            icon: 'success',
                            title: 'Tickets Found',
                            html: ticketsInfo,
                            showConfirmButton: true,
                            confirmButtonText: 'Close',
                            position: 'top-end',
                            timer: 20000 
                        });

                    } else {
                        // Clear the fields if no data is found
                        $('input[name="primary_no"]').val('');
                        $('select[name="title"]').val('');
                        $('input[name="fname"]').val('');
                        $('input[name="lname"]').val('');

                        Swal.fire({
                            toast: true,
                            icon: 'warning',
                            title: 'No Tickets Available',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        toast: true,
                        icon: 'error',
                        title: 'An error occurred',
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        }
    });
});


 function previewMultipleImages(event) {
        const previewsContainer = document.getElementById('previews');
        previewsContainer.innerHTML = ''; // Clear previous previews

        const files = event.target.files;

        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            const file = files[i];

            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('img-thumbnail');
                img.style.maxWidth = '100px';
                img.style.height = '100px';
                img.style.marginRight = '10px';
                img.style.marginTop = '10px';
                previewsContainer.appendChild(img);
            };

            // Read each selected file
            reader.readAsDataURL(file);
        }
    }
    $(document).ready(function() {
    // Listen to changes in the category_name and category_type fields
    $('#complaintCategory, #issue_type').on('change', function() {
        let categoryName = $('#complaintCategory').val();
        let categoryType = $('#issue_type').val();

        // Make sure both are selected before making the request
        if (categoryName && categoryType) {
            $.ajax({
                url: '{{ route("getComplaintDetails") }}',  // Ensure this is the correct route
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    category_name: categoryName,
                    category_type: categoryType
                },
                success: function(response) {
    // Clear the previous options in the issueDetail dropdown
    $('#issueDetail').empty().append('<option value="">Select An Issue</option>');

    // Check if response has data
    if (response.length > 0) {
        // Sort the response array alphabetically by 'category_detail'
        response.sort(function(a, b) {
            var categoryA = a.category_detail.toUpperCase(); // Ignore case
            var categoryB = b.category_detail.toUpperCase(); // Ignore case
            if (categoryA < categoryB) {
                return -1;
            }
            if (categoryA > categoryB) {
                return 1;
            }
            return 0;
        });

        // Populate the issueDetail dropdown with the sorted data
        $.each(response, function(key, value) {
            $('#issueDetail').append('<option value="' + value.id + '">' + value.category_detail + '</option>');
        });
    }else {
                        // If no data, display a message
                        $('#issueDetail').append('<option value="">No Issues Found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                }
            });
        }
    });
});
    document.getElementById('issueDetail').addEventListener('change', function() {
        var selectedCategory = this.options[this.selectedIndex].text;  // Get the text of the selected option
    var descriptionHelp = document.getElementById('descriptionHelp');
    if (selectedCategory === 'Top-up Not Reflecting' || selectedCategory === 'Bundle Not Reflecting') {
        descriptionHelp.textContent = 'Describe the Transaction ID (TXN), Method Used (USSD, APP), and Number deducted';
        doeHelp.textContent = 'Date when transaction Happened';
    } else if(selectedCategory === 'No Internet Access' || selectedCategory === 'Network Outage' || selectedCategory === 'Poor Network Coverage' || selectedCategory === 'No Network') {
        descriptionHelp.textContent = 'Clearly Describe the Complaint';
        doeHelp.textContent = 'Date or Period When it started';
    } else {
        descriptionHelp.textContent = 'Clearly Describe the issue, for Unable to Make/Receive Calls Ask for the Other Number.';
    }
});
document.getElementById('issueDetail').addEventListener('change', function() {
    var selectedCategory = this.options[this.selectedIndex].text;
    var descriptionField = document.getElementById('issue_description');
    var doeField = document.getElementById('duration_of_experience');
    var issuestatusField = document.getElementById('issuestatus');
    var deviceField = document.getElementById('device');
    var locationField = document.getElementById('location');
    var interactionField = document.getElementById('interactionstatus');
    var rootField = document.getElementById('root');
    // Check if the selected category is 'Billing'
});
// Remove red border when user types in the descriptionField
document.getElementById('issue_description').addEventListener('input', function() {
    this.style.border = ''; // Clear red border when typing
});

// Remove red border when user types in the doeField
document.getElementById('duration_of_experience').addEventListener('input', function() {
    this.style.border = ''; // Clear red border when typing
});
document.getElementById('interactionstatus').addEventListener('input', function() {
    this.style.border = ''; // Clear red border when typing
});
document.getElementById('root').addEventListener('input', function() {
    this.style.border = ''; // Clear red border when typing
});
document.getElementById('device').addEventListener('input', function() {
    this.style.border = ''; // Clear red border when typing
});
document.getElementById('location').addEventListener('input', function() {
    this.style.border = ''; // Clear red border when typing
});
document.getElementById('issuestatus').addEventListener('input', function() {
    this.style.border = ''; // Clear red border when typing
});
document.getElementById('msisd').addEventListener('input', function () {
            var msisdnInput = this.value;
            var searchBtn = document.getElementById('search-btn');
            var digitWarning = document.getElementById('digit-warning');

            // Enable search button only if MSISDN length is 10
            if (msisdnInput.length === 10) {
                searchBtn.disabled = false;
                digitWarning.style.display = 'none';
            } else {
                searchBtn.disabled = true;
                if (msisdnInput.length > 0 && msisdnInput.length < 10) {
                    digitWarning.style.display = 'block';
                } else {
                    digitWarning.style.display = 'none';
                }
            }
        });
</script>

</body>
</html>
