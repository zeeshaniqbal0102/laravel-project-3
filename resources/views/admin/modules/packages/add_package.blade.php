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
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-rose">
                    <div class="card-icon">
                    <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title">Add Package
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('packages.store', ['userType' => $userType]) }}">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                <label>Thumbnail Color *</label>
                                <input type="text" name="thumbnail_color" id="thumbnail_color" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" id="name" required class="form-control package-required">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                <label>Description *</label>
                                <input type="text" name="description" id="description" required class="form-control package-required">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                <label>Rate *</label>
                                <input type="number" name="rate" id="rate" required class="form-control package-required">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                <label>Call Minutes *</label>
                                <input type="number" name="max_questions" id="max_questions" required class="form-control package-required">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                <label>Sequence *</label>
                                <input type="number" name="sequence" id="sequence" required class="form-control package-required">
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('packages.index', ['userType' => $userType]) }}" class="btn btn-rose pull-right">Back</a>
                        <button class="btn btn-rose pull-right" type="submit">Save</button>
                        <div class="clearfix"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection