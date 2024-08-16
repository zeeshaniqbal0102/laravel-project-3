@extends('layouts.maincontent')

@section('content')
    <div class="content">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        @if(Session::has('message'))
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
                        @endif
                            {{-- @include('partials.messages') --}}
                        <div class="card">
                            <div class="card-header card-header-rose card-header-icon">
                                <div class="card-icon">
                                    <i class="material-icons">receipt</i>
                                </div>
                                <h3 class="card-title text-primary">New Invoice</h3>

                            </div>
                            <div class="card-body">
                                <br>
                                  
                                <form method="POST" action="{{ route('reader_invoice.store',['userType' => $role]) }}" class="col-md-10" id="package-form">
                                    @csrf
                                    @if($isAdmin)
                                        <div class="row">
                                            <label class="col-sm-2 col-form-label">Reader <span class="text-danger">*</span></label>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <select required name="reader_id" class="form-control selectpicker" data-style="btn btn-primary" title="Choose a Reader">
                                                        <option value="">Select a reader</option>
                                                        <option value="0">All Reader</option>
                                                        @foreach($readers as $reader)
                                                        <option value="{{$reader->reader_id}}">{{$reader->reader_handle}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <label class="col-sm-2 col-form-label">Invoice Cut-off <span class="text-danger">*</span></label>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <select required name="cutoff" class="form-control selectpicker" data-style="btn btn-primary" >
                                                    <option value="">Select Invoice Cut-off</option>
                                                    @foreach($listOfCutoffs as $c)
                                                        <option value="{{$c}}">{{$c}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <label class="col-sm-2 col-form-label">Month <span class="text-danger">*</span></label>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <select required name="month" class="form-control selectpicker" data-style="btn btn-primary" >
                                                    <option value="">Select Month</option>
                                                    @for($i=1;$i<=12;$i++)
                                                    <option {{ ( in_array($i, $generatedMonths) ? '': '' ) }} 
                                                        value="{{$i}}">{{$i}} - {{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $previous_year = date("Y") - 1;
                                        $current_year = date("Y") + 5 ;
                                    @endphp

                                     <div class="row">
                                        <label class="col-sm-2 col-form-label">Year <span class="text-danger">*</span></label>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                {!! Form::selectRange('year', $previous_year, $current_year, null, array('class' => 'form-control selectpicker', 'data-style'=> "btn btn-primary" )) !!} 

                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <div class="card-footer ml-auto mr-auto mb-4 text-left">
                                <button type="submit" class="btn btn-fill btn-rose" form="package-form">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('reader_invoice.index',['userType' => $role]) }}" class="btn btn-primary btn-round">Go Back</a>
            </div>
        </div>
    </div>
@endsection

