@extends('layouts.maincontent')
@section('content')
@if($errors->any())
    
    <div class="alert alert-danger">
        {!! implode('', $errors->all('<div>:message</div>')) !!}
    </div>
    
@endif
@if(session()->has('success'))
    <div class="alert alert-success">
        {{ session()->get('success') }}
    </div>
@endif
@if(session()->has('error'))
    <div class="alert alert-error">
        {{ session()->get('error') }}
    </div>
@endif
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@voerro/vue-tagsinput@2.7.1/dist/style.css">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-rose">
                    <div class="card-icon">
                    <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title">Credit Adjustment
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row mt-3">
                        <div class="col-12 col-md-8">
                            <form method="POST" class="container" action="{{ route('clients.adjust_credit', ['userType' => $userType, 'id' => $row->id]) }}">
                                <input type="hidden" name="_method" value="PUT">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 col-12">
                                        <div class="form-group">
                                            <label class="bmd-label-floating">Firstname</label>
                                            <input type="text" class="form-control" value="{{ $row->username }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="form-group">
                                            <label class="bmd-label-floating">Lastname</label>
                                            <input type="text" value="{{ $row->last_name }}" class="form-control" disabled>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 col-6">
                                        <div class="form-group">
                                            <label class="bmd-label-floating">Email</label>
                                            <input type="text" class="form-control" value="{{ $row->email }}" disabled>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12 col-12">
                                        <div class="form-group">
                                            <label class="bmd-label-floating">Avail. Credit</label>
                                            <input type="text" class="form-control" value="{{ $row->credits }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="form-group">
                                            <label class="bmd-label-floating">Adjustment</label>
                                            <select name="type" class="selectpicker" data-size="7" data-style="btn btn-primary" title="Select Adjustment">
                                                <option value="" selected>Select Adjustment</option>
                                                <option value="addition">Addition</option>
                                                <option value="deduction">Deduction</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 col-6">
                                        <div class="form-group">
                                            <label class="bmd-label-floating">Credit Adjustment</label>
                                            <input type="number" class="form-control" name="credits">
                                        </div>
                                    </div>
                                </div>
        
                                <a href="{{ route('clients.index', ['userType' => $userType]) }}" class="btn btn-rose pull-right">Back</a>
                                <button class="btn btn-rose pull-right" type="submit">Save</button>
                                <div class="clearfix"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection