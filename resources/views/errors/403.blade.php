@extends('layouts.illustrated-layout')

@section('image', asset('images/covers/traffic_lights.jpg'))
@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))
