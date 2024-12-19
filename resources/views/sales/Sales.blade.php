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
                    <div class="card bg-body shadow-sm">
                        <div class="card-header p-3 text-white" style="background-color: #0A2558;">
                            New Sales Record
                        </div>
                        <div class="card-body">
                            <form action="{{ route('create-Sales') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <!-- Customer's Personal Information -->
                                <fieldset class="border p-4 mb-4">
                                    <legend class="w-auto">Customer's Personal Information</legend>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="msisdn">MSISDN</label>
                                            <input type="text" class="form-control bg-body shadow-sm border-success" name="msisdn" id="msisdn" value="{{ old('msisdn') }}" autocomplete="off" aria-describedby="msisdnHelp">
                                            @error('msisdn')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <small id="msisdnHelp" class="form-text text-muted">Enter your phone number.</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="primary_no">Alternative Number</label>
                                            <input type="text" class="form-control bg-body shadow-sm" name="primary_no" id="primary_no" value="{{ old('primary_no') }}" autocomplete="off">
                                            @error('primary_no')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3">
                                            <label for="nrc">NRC</label>
                                            <input type="text" class="form-control bg-body shadow-sm" name="nrc" id="nrc" value="{{ old('nrc') }}" autocomplete="off">
                                            @error('nrc')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="title">Title</label>
                                            <select class="custom-select bg-body shadow-sm" name="title" id="title" >
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
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="fname">First Name</label>
                                            <input type="text" class="form-control bg-body shadow-sm" name="fname" id="fname" value="{{ old('fname') }}" required autocomplete="off">
                                            @error('fname')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="lname">Last Name</label>
                                            <input type="text" class="form-control bg-body shadow-sm" name="lname" id="lname" value="{{ old('lname') }}" required autocomplete="off">
                                            @error('lname')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Sales Details -->
                                <fieldset class="border p-4 mb-4">
    <legend class="w-auto">Sales Details</legend>

        
        <!-- Dynamic Sales Fields -->
        <div id="salesDetailsContainer">
            <div class="sales-detail-row mb-3" id="sales-detail-row-1">
                <div class="row">
                    <div class="col-md mb-3">
                        <label for="product_id_1">Product Name*</label>
                        <select class="custom-select bg-body shadow-sm" name="product_id[]" id="product_id_1" required>
                            <option value="" selected>Select Product</option>
                            @foreach($products as $cat)
                                <option value="{{ $cat->id }}" {{ old('product_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->product }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md">
                        <label for="quantity_1">Quantity</label>
                        <input type="number" class="form-control bg-body shadow-sm" id="quantity_1" name="quantity[]" min="1" max="100" required>
                        @error('quantity.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md">
                        <label for="payment_type_1">Payment Method</label>
                        <select class="custom-select bg-body shadow-sm" name="payment_type[]" id="payment_type_1" required>
                            <option value="">Select Payment Method</option>
                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank" {{ old('payment_type') == 'bank' ? 'selected' : '' }}>Bank</option>
                            <option value="airtel money" {{ old('payment_type') == 'airtel money' ? 'selected' : '' }}>Airtel Money</option>
                            <option value="mtn money" {{ old('payment_type') == 'mtn money' ? 'selected' : '' }}>MTN Money</option>
                            <option value="zed wallet" {{ old('payment_type') == 'zed wallet' ? 'selected' : '' }}>Zed Wallet</option>
                        </select>
                        @error('payment_type.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md">
                        <label for="amount_1">Amount (ZMW)</label>
                        <input type="text" class="form-control bg-body shadow-sm" id="amount_1" name="amount[]" required>
                        @error('amount.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-1 align-self-center">
                        <button type="button" class="btn btn-danger remove-row-btn" data-row-id="1">Remove</button>
                    </div>
                </div>
            </div>
           
    </div>
    <div class="row mt-3">
        <div class="col-lg-6">
            
            <label for="issue_description">Notes</label>
                                        <textarea class="form-control bg-body shadow-sm" name="notes" rows="4" id="issue_description"></textarea>
                                     
                                        @error('notes')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
           
    </div>
    <div class="col-lg">
    <label for="volte-upsell" class="form-label">VoLTE Upsell</label>
        <div id="volte-upsell">
            <div class="form-check form-check-inline">
                <input 
                    class="form-check-input" 
                    type="radio" 
                    name="volte_upsell" 
                    id="volte_upsell_yes" 
                    value="yes" 
                    required>
                <label class="form-check-label" for="volte_upsell_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input 
                    class="form-check-input" 
                    type="radio" 
                    name="volte_upsell" 
                    id="volte_upsell_no" 
                    value="no">
                <label class="form-check-label" for="volte_upsell_no">No</label>
            </div>
        </div>
                </div>
                <div class="col-lg">
                <label for="zedlife-upsell" class="form-label">Zedlife App Upsell</label>
        <div id="zedlife-upsell">
            <div class="form-check form-check-inline">
                <input class="form-check-input" 
                    type="radio" 
                    name="zedlife_upsell" 
                    id="zedlife_upsell_yes" 
                    value="yes" 
                    required>
                <label class="form-check-label" for="zedlife_upsell_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input 
                    class="form-check-input" 
                    type="radio" 
                    name="zedlife_upsell" 
                    id="zedlife_upsell_no" 
                    value="no">
                <label class="form-check-label" for="zedlife_upsell_no">No</label>
            </div>
                </div>
        </div>
    

       
        <!-- Submit Button -->
        <div class="row m-3 mb-3">
            <div class="col">
                 <!-- Add New Row Button -->
        <button type="button" class="btn btn-success" id="addNewRowBtn">Add Another Product</button>

            </div>
            <div class="col">
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

@include('dashboard.script')

<!-- You can include additional JavaScript/jQuery for dynamic form interactions -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
 $(document).ready(function() {
    $('#msisdn').on('input', function() {
        var msisdn = $(this).val();

        // Check if exactly 10 digits are typed
        if (msisdn.length === 10) {
            $.ajax({
                url: "{{ route('check.sales') }}",  // Ensure this route is defined in your routes file
                method: "GET",
                data: { msisdn: msisdn },
                success: function(response) {
                    if (response.status === 'found') {
                        // Populate the fields with the retrieved data
                        $('input[name="primary_no"]').val(response.data.primary_no);
                        $('input[name="nrc"]').val(response.data.nrc);
                        $('select[name="title"]').val(response.data.title);
                        $('input[name="fname"]').val(response.data.fname);
                        $('input[name="lname"]').val(response.data.lname);

                        // Prepare the ticket info for the pop-up
                        var ticketsInfo = '';
                        $.each(response.sales, function(index, sale) {
                            console.log(sale); 
                            ticketsInfo += '<div class="sale-info">' +
                                           '<span class="sale-id-status">' +
                                          '<strong>Product:</strong> ' + sale.product_id + ' | ' +
                                           '<strong>Quantity:</strong> ' + sale.quantity + ' | ' +
                                           '<strong>Created At:</strong> ' + sale.created_at + '</span><br>' +
                                           '<strong>Amount:</strong> K' + sale.amount + '<hr>'
                                           '</div>';
                        });

                        // Show the pop-up with ticket information
                        Swal.fire({
                            toast: false,
                            icon: 'success',
                            title: 'Sales Found',
                            html: ticketsInfo,
                            showConfirmButton: true,
                            confirmButtonText: 'Close',
                            position: 'top-end',
                            timer: 20000 
                        });

                    } else {
                        // Clear the fields if no data is found
                        $('input[name="primary_no"]').val('');
                        $('input[name="nrc"]').val('');
                        $('select[name="title"]').val('');
                        $('input[name="fname"]').val('');
                        $('input[name="lname"]').val('');

                        Swal.fire({
                            toast: true,
                            icon: 'warning',
                            title: 'No Sales Available',
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
    let rowCount = 1;

    document.getElementById('addNewRowBtn').addEventListener('click', function() {
        rowCount++;
        const newRow = document.createElement('div');
        newRow.classList.add('sales-detail-row', 'mb-3');
        newRow.id = `sales-detail-row-${rowCount}`;
        newRow.innerHTML = `
            <div class="row">
                <div class="col-md mb-3">
                    <label for="product_id_${rowCount}">Product Name*</label>
                    <select class="custom-select bg-body shadow-sm" name="product_id[]" id="product_id_${rowCount}" required>
                        <option value="" selected>Select Product</option>
                        @foreach($products as $cat)
                            <option value="{{ $cat->id }}" {{ old('product_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->product }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id.*')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md">
                    <label for="quantity_${rowCount}">Quantity</label>
                    <input type="number" class="form-control bg-body shadow-sm" id="quantity_${rowCount}" name="quantity[]" min="1" max="100" required>
                    @error('quantity.*')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md">
                    <label for="payment_type_${rowCount}">Payment Method</label>
                    <select class="custom-select bg-body shadow-sm" name="payment_type[]" id="payment_type_${rowCount}" required>
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank" {{ old('payment_type') == 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="airtel money" {{ old('payment_type') == 'airtel money' ? 'selected' : '' }}>Airtel Money</option>
                        <option value="mtn money" {{ old('payment_type') == 'mtn money' ? 'selected' : '' }}>MTN Money</option>
                        <option value="zed wallet" {{ old('payment_type') == 'zed wallet' ? 'selected' : '' }}>Zed Wallet</option>
                    </select>
                    @error('payment_type.*')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md">
                    <label for="amount_${rowCount}">Amount (ZMW)</label>
                    <input type="text" class="form-control bg-body shadow-sm" id="amount_${rowCount}" name="amount[]" required>
                    @error('amount.*')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-1 align-self-center">
                    <button type="button" class="btn btn-danger remove-row-btn" data-row-id="${rowCount}">Remove</button>
                </div>
            </div>
        `;
        document.getElementById('salesDetailsContainer').appendChild(newRow);

        // Add event listener for the remove button of the new row
        newRow.querySelector('.remove-row-btn').addEventListener('click', function() {
            const rowId = this.getAttribute('data-row-id');
            document.getElementById(`sales-detail-row-${rowId}`).remove();
        });
    });

    // Event listener for removing rows
    document.querySelectorAll('.remove-row-btn').forEach(button => {
        button.addEventListener('click', function() {
            const rowId = this.getAttribute('data-row-id');
            document.getElementById(`sales-detail-row-${rowId}`).remove();
        });
    });
</script>

</body>
</html>
