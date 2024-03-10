@extends('layouts.illustrated-layout')
@php
if (!$exception instanceof \App\Exceptions\Referencable) {
    $exception = $exception->getPrevious();
}
@endphp

@section('image', asset('images/covers/derailment.jpg'))
@section('title', __('error.500'))
@section('code', '500')
@section('message', __('error.500'))
@section('reference', errorMessage($exception, ''))
