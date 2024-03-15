@extends('layouts.illustrated-layout')

@section('image', asset('images/covers/traffic_lights.jpg'))
@section('title', __('error.403'))
@section('code', '403')
@section('message', $exception->getMessage() ?: __('error.403'))
