@extends('layouts.app')

@section('title')
    Privacy
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>Changelog</h1>
                <h5>{{ get_current_git_commit() }}</h5>
                <p>{{ get_current_git_commit_message() }}</p>
            </div>
        </div>
    </div><!--- /container -->
@endsection
