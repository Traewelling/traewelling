@extends('layouts.illustrated-layout')

@section('image', \Illuminate\Support\Facades\Vite::asset('resources/images/covers/hp0.jpg'))
@section('title', __('error.403'))
@section('code', '403')
@section('message', $exception->getMessage() ?: __('error.403'))
