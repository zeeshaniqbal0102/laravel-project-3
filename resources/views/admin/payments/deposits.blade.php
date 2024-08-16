@extends('layouts.maincontent')

@section('content')

    <div class="content">
        <div class="content">
            <div class="container-fluid">
                @if(Session::has('message'))
                    <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-header-primary card-header-icon">
                                <div class="card-icon">
                                    <i class="material-icons">assignment</i>
                                </div>
                                <h3 class="card-title text-primary">Payment Details</h3>

                            </div>
                            <div class="card-body">
                                <div class="row">
                                   
                                    <div class="col-md-6">
                                        <form action="{{ route('admin.payment.deposit',['userType' => $role]) }}" method="_GET">
                                            <div class="row">
                                                <div class="col">From</div>
                                                <div class="col">
                                                    <input type="date" class="form-control" name="from" value="{{$_GET['from'] ?? '' }}">
                                                </div>
                                                <div class="col">To</div>
                                                <div class="col">
                                                    <input type="date" class="form-control" name="to" value="{{$_GET['to'] ?? '' }}">
                                                </div>
                                                <div class="col">
                                                    <button type="submit" class="btn btn-success btn-sm">Search</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-6">

                                    </div>

                                </div>
                                <div class="material-datatables table-responsive">
                                    <table id="datatable-summary" class="table table-striped table-no-bordered table-hover" cellspacing="0"
                                           width="100%" style="width:100%">
                                        <thead>
                                        <tr>
                                            <th class="text-center">Username</th>
                                            <th class="text-center">Order Number</th>
                                            <th class="text-center">Payment Method</th>
                                            <th class="text-center">Last 4</th>
                                            <th class="text-center">Brand</th>
                                            <th class="text-center">Amount</th>
                                            <th class="text-center">Package</th>
                                            <th class="text-center">Credits</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Transaction Date</th>
                                            <th class="text-center">Action</th>

                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr>
                                            <th class="text-center">Username</th>
                                            <th class="text-center">Order Number</th>
                                            <th class="text-center">Payment Method</th>
                                            <th class="text-center">Last 4</th>
                                            <th class="text-center">Brand</th>
                                            <th class="text-center">Amount</th>
                                            <th class="text-center">Package</th>
                                            <th class="text-center">Credits</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Transaction Date</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                        </tfoot>
                                        <tbody>
                                        @php
                                            $totalAmount = 0;
                                            $totalCredit = 0;
                                        @endphp
                                        @foreach($transactions as $key => $transaction)

                                            <tr>
                                                <td>{{ $transaction->username }}</td>
                                                <td>{{ $transaction->order_number }}</td>
                                                <td>{{ $transaction->payment_method }}</td>
                                                <td>{{ $transaction->last4 }}</td>
                                                <td>{{ $transaction->brand }}</td>
                                                <td>{{ $transaction->amount }}</td>
                                                <td>{{ $transaction->package }}</td>
                                                <td class="text-right">{{ $transaction->credits }}</td>

                                                <td class="text-center">
                                                    @php
                                                        switch ($transaction->payment_status) {
                                                            case 0:
                                                                echo "<p class='text-primary'><strong>For Deposit</strong></p>";
                                                                break;
                                                            case 1:
                                                                echo "<p class='text-success'><strong>Deposited</strong></p>";    
                                                                break;
                                                            case 1:
                                                                echo "<p class='text-danger'><strong>Refunded</strong></p>";    
                                                                break;
                                                            case 2:
                                                                echo "<p class='text-danger'><strong>Voided</strong></p>";    
                                                                break;
                                                        }
                                                        
                                                        
                                                    @endphp
                                                </td>
                                                <td class="text-right">{{ $transaction->created_at}}</td>
                                                <td class="text-right">
                                                    @php 
                                                        if ($transaction->payment_status == 0)
                                                            echo '<button type="button" class="btn btn-sm  btn-round btn-primary deposit" 
                                                                    id=' . $transaction->id .' data-amount='. $transaction->amount . '>Deposit</button>';

                                                        if ($transaction->payment_status == 0)
                                                            echo '<button type="button" class="btn btn-sm  btn-round btn-danger void" 
                                                                    id=' . $transaction->id .' data-amount='. $transaction->amount . '>Void</button>';

                                                    @endphp
                                                </td>
                                            </tr>
                                        @endforeach
                                        
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- end content-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
    });
</script>            

<script>
$(document).ready(function () {

    if($('#datatable-summary').length) {

        const initButtons = function() {

            $('.deposit').unbind('click')
            $('.void').unbind('click')

            $( ".deposit" ).click(function(e) {
                var id = this.id;
                var amount = jQuery(this).attr("data-amount");
                e.preventDefault();
                swal({
                    title: "Are you sure to deposit the selected transaction " + amount + " ?" ,
                    type: "info",
                    buttons: true,
                    dangerMode: false,
                }).then((value) => {
                    if(value) {
                        jQuery.ajax({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{ route('admin.payment.process.deposit',['userType' => $role]) }}",
                            method: 'POST',
                            data: {id: id},
                            success: function(response) {
                                console.log(response.data.message);
                                sweetAlert("Successfully posted card deposit.", response.data.message, "success");
                                location.href="{{ route('admin.payment.deposit',['userType' => $role]) }}";
                            },
                            error: function(data){
                                sweetAlert("Error!", "There is an issue with the card deposit!", "error");
                            }
                        });
                    }
                });

            });


            $( ".void" ).click(function(e) {
                var id = this.id;
                var amount = jQuery(this).attr("data-amount");
                e.preventDefault();
                swal({
                    title: "Are you sure to void the selected transaction " + amount + " ?" ,
                    type: "info",
                    buttons: true,
                    dangerMode: false,
                }).then((value) => {
                    if(value) {
                        jQuery.ajax({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                            },
                            url:"{{ route('admin.payment.process.void',['userType' => $role]) }}",
                            method: 'POST',
                            data: {id: id},
                            success: function(response) {
                                console.log(response.data.message);
                                sweetAlert("Successfully voided the  payment or deposit.", response.data.message, "success");
                                location.href="{{ route('admin.payment.deposit',['userType' => $role]) }}";
                            },
                            error: function(data){
                                sweetAlert("Error!", "There is an issue with the void!", "error");
                            }
                        });
                    }
                });
            });
        }

        $('#datatable-summary').DataTable({
            "pagingType": "full_numbers",
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            responsive: true,
            "order": [[ 9, "desc" ]],

            'columnDefs'        : [       
                { 
                    'searchable'    : false, 
                    'targets'       : [1,2,3,4,5,6,7,8,9] 
                },
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Username",
            },
            initComplete: function(settings, json) {

                initButtons()

                $('#datatable-summary tbody > tr > td').click(function() {
                    setTimeout(() => {
                        initButtons()
                    }, 1000)
                })
                
            },
        });
    }
})
</script>

@endsection
