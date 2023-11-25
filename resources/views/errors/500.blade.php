@extends('layouts.illustrated-layout')
@php
if (!$exception instanceof \App\Exceptions\Referencable) {
    $exception = $exception->getPrevious();
}
@endphp

@section('image', asset('images/covers/derailment.jpg'))
@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Server Error'))
@section('reference', errorMessage($exception, ''))
