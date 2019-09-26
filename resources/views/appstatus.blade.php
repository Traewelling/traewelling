@extends('layouts.app')

@section('title')
    Application Status
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>Application Status <code>{{ substr(get_current_git_commit(), 0, 6) }}</code></h1>
            </div>
        </div>
    </div><!--- /container -->
@endsection
