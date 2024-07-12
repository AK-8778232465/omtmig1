<div class="col-md-12 pl-2">
    <div class="card ">
        <div class="card-body tabborder">
            <div class="d-flex flex-grow-1 flex-wrap justify-content-md-start justify-content-lg-center"
                style=" align-content: center;align-items: center;">
                    <div class="pl-1 pb-1"><a id="tab1" class="btn  {{ (Request::is('settings') || Request::is('settings/users')) ? 'btn-warning' : 'btn-primary' }}" href="{{route('users')}}">Users</a></div>
                    <div class="pl-1 pb-1"><a id="tab1" class="btn  {{ Request::is('settings/sduploads') ? 'btn-warning' : 'btn-primary' }}" href="{{route('sduploads')}}">Supporting Docs Upload</a></div>
            </div>
        </div>
    </div>
</div>
