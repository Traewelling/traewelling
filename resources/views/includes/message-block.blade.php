@if ($errors->any())
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                @foreach ($errors->all() as $error)
                    <div class="alert my-3 alert-danger" role="alert">
                        {!! $error !!}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
@if(Session::has('message'))
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="alert my-3 alert-info" role="alert">
                    {{Session::get('message')}}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
