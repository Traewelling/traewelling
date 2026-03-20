@extends('admin.layout')

@section('title', 'Report ' . $reportId)

@section('content')
    <div id="vue-reports-show" data-report-id="{{ $reportId }}"></div>
@endsection
