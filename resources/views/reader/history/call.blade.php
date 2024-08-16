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
                                <h3 class="card-title text-primary">Call History</h3>
                            </div>
                            <div class="card-body">
                                <div class="material-datatables table-responsive">

                                    <table id="datatable" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>Session Id</th>
                                            <th>Client</th>
                                            <th>Call Minutes</th>
                                            <th>Date</th>
                                            <th>Action</th>

                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr>
                                            <th>Session Id</th>
                                            <th>Client</th>
                                            <th>Call Minutes</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                        </tfoot>
                                        <tbody>
                                        @foreach($data as $key => $value)
                                            <tr>
                                                <td>{{ $value->session_id }}</td>
                                                <td>{{ $value->client_handle  }}</td>
                                                <td>{{ $value->call_minutes }}</td>
                                                <td>{{ date('M d, Y h:i:s', strtotime($value->created_at)) }}</td>
                                                <th>
                                                    
                                                    
                                                        <button id={{$value->session_id}} type="button" rel="tooltip" class="btn btn-danger btn-link delete" data-original-title="" title="Delete/Archive">
                                                            <i class="material-icons">close</i>
                                                        </button>

                                                </th>
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

    const initButtons = () => {
        $('.delete').unbind('click')

        
        $( ".delete" ).click(function(e) {
            
            var id = $(this).attr('id');
            e.preventDefault();
            swal({
                title: "Are you sure to delete/archive the selected record " +id + " ?" ,
                type: "info",
                buttons: true,
                dangerMode: false,
            }).then((value) => {
                if(value) {
                    jQuery.ajax({
                        headers: {
                            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('client.call_history.delete',['userType' => $userType] ) }}",
                        method: 'POST',
                        data: {id: id},
                        success: function(response) {
                            console.log(response.status);
                            sweetAlert("Successfully Deleted Record.", response.message, "success");
                            location.reload(true);
                        },
                        error: function(data){
                            sweetAlert("Error!", "There is an issue with the delete!", "error");
                        }
                    });
                }
            });

        });
    }

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
                },
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Username",
            },
            initComplete: function(settings, json) {

                initButtons()

                $('#datatable tbody > tr > td').click(function() {
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
