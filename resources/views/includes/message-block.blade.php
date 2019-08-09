@if(count($errors) > 0)
    <div class="row justify-content-md-center">
        @foreach($errors->all() as $error)
            <div class="alert alert-danger" role="alert">
                {{$error}}
            </div>
        @endforeach
    </div>
@endif
@if(Session::has('message'))
    <div class="row">
        <div class="alert alert-info" role="alert">
            {{Session::get('message')}}
        </div>
    </div>
@endif
