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
                                <form action="{{ route('ticket') }}" method="GET">
            @csrf
            <div class="input-group">
            <input type="text" class="form-control" id="msisd" name="site_name"  placeholder="Search Profile By Site Name"  autocomplete = "off">
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
                                        <a href="{{ route('noc_tickets.SiteProfile', ['site_name' => $ticket->site_name]) }}">{{ $ticket->site_name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
    </div>
    </div>
                        <div class="card-body">
                        <form method="POST" action="{{ route('log-ticket') }}" enctype="multipart/form-data">
    @csrf

    <fieldset class="border p-4 mb-4">
        <legend class="w-auto">Site Fault Details</legend>
        <div class="row mt-3">
            <div class="col-lg">
                <label for="site_name">Site Name</label>
                <input type="text" class="form-control bg-body shadow-sm" name="site_name" id="site_name" value="{{ old('site_name') }}" autocomplete= "off">
                @error('site_name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-lg mb-3">
                <label for="sla_id">Fault Type</label>
                <select class="form-control bg-body shadow-sm" name="sla_id" id="sla_id">
                    <option value="" selected>Select Site Priority</option>
                    @foreach($site_sla as $sla)
                        <option value="{{ $sla->id }}" {{ old('sla_id') == $sla->id ? 'selected' : '' }}>
                            {{ $sla->fault_type }}
                        </option>
                    @endforeach
                </select>
                @error('sla_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg">
            <label for="dateTime">Fault Occurrence Time</label>
                <input type="datetime-local" id="dateTime" name="fault_occurrence_time" class="form-control bg-body shadow-sm" value="{{ old('fault_occurrence_time') }}">
                <small id="dateTimeHelp" class="form-text text-muted">Please select the date and time.</small>
                @error('fault_occurrence_time')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-lg">
                <label>Fault Severity</label>
                <select name="fault_severity" class="form-control bg-body shadow-sm" id="root">
                    <option value="">Select Fault Severity</option>
                    <option value="critical" {{ old('fault_severity') == 'critical' ? 'selected' : '' }}>Critical - Immediate action required</option>
                    <option value="high" {{ old('fault_severity') == 'high' ? 'selected' : '' }}>High - Major impact on operations</option>
                    <option value="medium" {{ old('fault_severity') == 'medium' ? 'selected' : '' }}>Medium - Moderate impact on operations</option>
                    <option value="low" {{ old('fault_severity') == 'low' ? 'selected' : '' }}>Low - Minor impact on operations</option>
                    <option value="informational" {{ old('fault_severity') == 'informational' ? 'selected' : '' }}>Informational - No immediate action needed</option>
                </select>
                <small id="rootHelp" class="form-text text-muted"></small>
                @error('fault_severity')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg">
                <label for="issue_description">Fault Description</label>
                <textarea class="form-control bg-body shadow-sm" name="fault_description" id="issue_description" rows="4">{{ old('fault_description') }}</textarea>
                <small id="descriptionHelp" class="form-text text-muted"></small>
                @error('fault_description')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-lg">
                <fieldset class="border p-4 mb-4">
                    <legend class="w-auto">Attachments</legend>
                    <div class="mb-3">
                        <label for="attachments">Attachments (Images)</label>
                        <input type="file" class="form-control bg-body shadow-sm" name="attachments[]" id="attachments" accept="image/*" multiple onchange="previewMultipleImages(event)">
                        <div id="previews" class="mt-2" style="display: flex; flex-wrap: wrap;"></div>
                        @error('attachments')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </fieldset>
            </div>
        </div>
    </fieldset>

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
<!-- Toast Notifisla_idion Structure -->
<div id="toast-container" style="position: fixed; top: 10px; right: 10px;"></div>


@include('dashboard.script')

<!-- You can include additional JavaScript/jQuery for dynamic form interactions -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoYz1zWbFOUT1a6DkZpvv9SOAzFcuGy4Lkfz1FO6DOMoarI" crossorigin="anonymous"></script>
<script>
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
</script>

</body>
</html>
