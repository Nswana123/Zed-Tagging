
<!DOCTYPE html>
<html>
<head>
    <title>Customer Support| CRM</title>
    @include('dashboard.style')
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/dashboard/style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>

nav {
    width: 100%;
    position: fixed; /* Keeps the header fixed at the top */
    top: 0;
    z-index: 1000;
}
        </style>
</head>
<body>
 
@include('dashboard.sidebar')
<div class="home-section">
@include('dashboard.header')

@include('dashboard.main-body')
</div>
@include('dashboard.script')

<script>    
let issueChart;  
function createIssueChart(issueDetails, issueCounts) {
    const ctx = document.getElementById('issueDetailsChart').getContext('2d');

    if (issueChart) {
        issueChart.destroy();  
    }

    issueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: issueDetails, 
            datasets: [{
                label: 'Number of Issues',
                data: issueCounts,  
                backgroundColor: '(0,0,255)', 
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                x: {
                    display: true
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const issueDetails = {!! json_encode($issueDetailsData->pluck('category_detail')) !!};
    const issueCounts = {!! json_encode($issueDetailsData->pluck('count')) !!};

    createIssueChart(issueDetails, issueCounts);
});

function fetchData(filter) {
    $.ajax({
        url: '/dashboard/chart-data/' + filter,
        method: 'GET',
        success: function (response) {
            console.log('Response:', response);  // Log the response to the console
            const issueDetails = response.map(item => item.category_detail);
            const issueCounts = response.map(item => item.count);
            createIssueChart(issueDetails, issueCounts);  // Update the chart with new data
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', xhr.responseText);  // Log error response
            alert('Failed to fetch data.');
        }
    });
}

let deviceChart; 
function createChart(customerDevices, deviceCounts) {
    const ctx = document.getElementById('customerDevicesChart').getContext('2d');
    if (deviceChart) {
        deviceChart.destroy();
    }
    deviceChart = new Chart(ctx, {
        type: 'bar',  
        data: {
            labels: customerDevices,  
            datasets: [{
                label: 'Number of Devices', 
                data: deviceCounts, 
                backgroundColor: 'rgba(0, 0, 255, 0.5)', 
                borderColor: 'rgba(54, 162, 235, 1)', 
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                x: {
                    display: true
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    // Combine brand and model into a single label
    const customerDevices = {!! json_encode($customerDevicesChart->map(fn($device) => $device->brand . ' ' . $device->model)) !!};
    const deviceCounts = {!! json_encode($customerDevicesChart->pluck('count')) !!};
    
    // Pass the labels and counts to your chart creation function
    createChart(customerDevices, deviceCounts);
});
function fetchDeviceData(filter) {
    $.ajax({
        url: '/dashboard/chart-devicedata/' + filter,
        method: 'GET',
        success: function (response) {
            const customerDevices = response.map(item => item.brand + ' ' + item.model); // Combine brand and model
            const deviceCounts = response.map(item => item.count);
            createChart(customerDevices, deviceCounts);
        },
        error: function () {
            alert('Failed to fetch data.');
        }
    });
}


let physicalAddressChart;

// Function to create the physical address chart
function createphysicalAddressChart(addresses, counts) {
    const ctx = document.getElementById('physicalAddressChart').getContext('2d');

    // Destroy the previous chart instance if it exists
    if (physicalAddressChart) {
        physicalAddressChart.destroy();
    }

    // Create a new chart
    physicalAddressChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: addresses,
            datasets: [{
                label: 'Number of Locations',
                data: counts,
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                x: {
                    display: true
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Function to fetch the data for the chart based on the filter
function fetchLocationData(filter) {
    $.ajax({
        url: '/dashboard/chart-locationdata/' + filter, // Dynamic filter in the URL
        method: 'GET',
        success: function (response) {
            const addresses = response.map(item => item.province + ' ' + item.town+' '+item.landmark); // Extract physical addresses
            const counts = response.map(item => item.count); // Extract the counts
            createphysicalAddressChart(addresses, counts); // Update the chart with the fetched data
        },
        error: function () {
            alert('Failed to fetch data.');
        }
    });
}

// Initialize the chart with default data (for example, daily data) after page load
document.addEventListener("DOMContentLoaded", function () {
    fetchLocationData('day'); // Fetch 'day' data by default
});
// Route Cause
let routeCauseChart;
function createRouteCauseChart(routeCauses, counts) {
    const ctx = document.getElementById('routeCauseChart').getContext('2d');

    if (routeCauseChart) {
        routeCauseChart.destroy();
    }

    routeCauseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: routeCauses,
            datasets: [{
                label: 'Number of Route Causes',
                data: counts, 
                backgroundColor: 'rgba(75, 192, 192, 0.5)', 
                borderColor: 'rgba(75, 192, 192, 1)', 
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                x: {
                    display: true
                },
                y: {
                    beginAtZero: true 
                }
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    fetchRouteCauseData('day');
});

function fetchRouteCauseData(filter) {
    $.ajax({
        url: '/dashboard/chart-routecause/' + filter,
        method: 'GET',
        success: function (response) {
            const routeCauses = response.map(item => item.root_cause);
            const counts = response.map(item => item.count);
            createRouteCauseChart(routeCauses, counts);
        },
        error: function () {
            alert('Failed to fetch data.');
        }
    });
}    
// Route Cause
const fetchFaultsitesData = (filter) => {
        fetch(`/dashboard/chart-faultysites/${filter}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then((data) => {
                renderChart(data);
            })
            .catch((error) => {
                console.error('Error fetching data:', error);
            });
    };

    const renderChart = (data) => {
        const ctx = document.getElementById('faultySitesData').getContext('2d');
        
        // Check if chart instance exists to avoid duplicate rendering
        if (window.faultySitesChart) {
            window.faultySitesChart.destroy();
        }

        window.faultySitesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map((item) => item.site_name),
                datasets: [
                    {
                        label: 'Serviced Count',
                        data: data.map((item) => item.count),
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    };

    // Fetch default data for "day" filter on page load
    document.addEventListener('DOMContentLoaded', () => fetchFaultsitesData('day'));    
    let faultTypeChart;

function createFaultTypeChart(faultTypes, faultCounts) {
    const ctx = document.getElementById('faultTypeChart').getContext('2d');

    // Destroy existing chart instance to avoid duplication
    if (faultTypeChart) {
        faultTypeChart.destroy();
    }

    faultTypeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: faultTypes, // Labels are the fault types
            datasets: [
                {
                    label: 'Number of Faults',
                    data: faultCounts, // Data is the count of faults
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    display: true,
                },
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
}

// Fetch data and update the chart based on the selected filter
function fetchFaultytypeData(filter) {
    $.ajax({
        url: '/dashboard/chart-faultytype/' + filter,
        method: 'GET',
        success: function (response) {
            const faultTypes = response.map((item) => item.fault_type);
            const faultCounts = response.map((item) => item.count);
            createFaultTypeChart(faultTypes, faultCounts);
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', xhr.responseText);
            alert('Failed to fetch data.');
        },
    });
}

// Default: Load "daily" data on page load
document.addEventListener('DOMContentLoaded', () => fetchFaultytypeData('day'));

    </script>

</body>
</html>
