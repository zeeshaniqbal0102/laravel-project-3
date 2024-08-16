<div class="col-sm-6 col-xs-12 col-md-4 col-lg-3 cards pb-5 mb-5 position-relative reader-item">
    <div class="position-absolute actions">
        <div class="rate w-100">
            <b>$ {{$reader->rate}} / min</b>
        </div>

        @if ($reader->phone_availability == 'Online')
            @if (Auth::check())
                <a href="{{ route('app', ['userType' => $userType]) . "?reader=$reader->id" }}" class="btn btn-primary py-2 px-3">Talk</a>
            @else
                <a href="{{ route('register2') . "?reader=$reader->id" }}" class="btn btn-primary py-2 px-3">Talk</a>
            @endif
        @else
            <button href="javascript:void(0)" class="btn btn-default py-2 px-3 disabled" disabled>Talk</button>
        @endif

        <a href="{{ route('about.reader', ['readerId' => $reader->id]) }}" class="btn btn-default py-2 px-3 mt-1">
            <i class="fa fa-info"></i> Read about
        </a>
    </div>
    <div class="card card-pricing card-raised">
        <div class="card-body">
            <h6 class="card-category"></h6>
            <div class="card-icon icon-rose position-relative">
                <div class="position-absolute m-2">
                    @switch($reader->phone_availability)
                        @case('Online')
                            <span class="badge badge-pill status badge-success p-2">Online</span>
                            @break
                        @case('Break')
                            <span class="badge badge-pill status badge-info p-2">Break</span>
                            @break
                        @case('Busy')
                            <span class="badge badge-pill status badge-info p-2">Busy</span>
                            @break
                        @case('Offline')
                            <span class="badge badge-pill status badge-secondary p-2">Offline</span>
                            @break
                        @default
                            
                    @endswitch
                </div>
                <div class="m-auto card-avatar">
                    <img src="{{ $reader->image_url }}" alt="" class="w-100"> 
                </div>
            </div>
            <h3 class="card-title mt-4"><b>{{ $reader->username }}</b></h3>
            <p class="card-description text-left">
                {{ $reader->area_of_expertise }}
            </p>
        </div>
    </div>
</div>
<style>
.reader-item .card-avatar, .card-profile .card-avatar {
    margin: -50px auto 0;
    border-radius: 10%;
    overflow: hidden;
    padding: 0;
    box-shadow: 0 16px 38px -12px rgb(0 0 0 / 56%), 0 4px 25px 0px rgb(0 0 0 / 12%), 0 8px 10px -5px rgb(0 0 0 / 20%);
}
.reader-item .card-avatar {
    max-width: 170px;
    max-height: 170px;
}

.reader-item .card {
    border-radius: 15px;
    border: unset;
}

.reader-item .actions {
    width: 100%;
    text-align: center;
    bottom: 0;
    z-index: 999;
}

.reader-item .card-pricing {
    border-top: 5px solid #7d3c92;
}
</style>