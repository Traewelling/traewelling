@if ($errors->any())
    <div class="row justify-content-center">
        <div class="col-md-8">
            @foreach ($errors->all() as $error)
                <div class="alert my-3 alert-danger" role="alert">
                    {{ $error }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endif
@if(Session::has('message'))
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert my-3 alert-info" role="alert">
                {{Session::get('message')}}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
@endif
