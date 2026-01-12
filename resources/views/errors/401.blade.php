@extends('layouts.illustrated-layout')

@section('image', \Illuminate\Support\Facades\Vite::asset('resources/images/covers/hp0.jpg'))
@section('title', __('error.401'))
@section('code', '401')
@section('message', __('error.401'))
