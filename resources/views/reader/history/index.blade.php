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
                                <h3 class="card-title text-primary">Payment History</h3>

                            </div>
                            <div class="card-body">
                                <div class="material-datatables table-responsive">

                                    <table id="datatable" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>Order No.</th>
                                            <th>Package</th>
                                            <th>Currency</th>
                                            <th>Payment Type</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr>
                                            <th>Order Number</th>
                                            <th>Package</th>
                                            <th>Currency</th>
                                            <th>Payment Method</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                        </tfoot>
                                        <tbody>
                                        @foreach($data as $key => $value)
                                            <tr>
                                                <td>{{ $value->order_number }}</td>
                                                <td>{{ $value->package }}</td>
                                                <td>{{ $value->currency }}</td>
                                                <td>{{ $value->payment_method }}</td>
                                                <td>{{ $value->amount }}</td>
                                                <td>{{ date('M d, Y h:i:s', strtotime($value->created_at)) }}</td>
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
    if($('#datatable').length) 
    {
        $('#datatable').DataTable({
            "pagingType": "full_numbers",
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            responsive: true,
            'columnDefs'        : [       
                { 
                    'searchable'    : false, 
                    'targets'       : [1,2,3,4,5] 
                },
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Order Number",
            }
        });
    }
})
</script>

@endsection
