@extends('layouts.illustrated-layout')

@section('image', \Illuminate\Support\Facades\Vite::asset('resources/images/covers/abandoned.jpg'))
@section('title', __('error.404'))
@section('code', '404')
@section('message', __('error.404'))
