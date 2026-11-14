@extends('layouts.tailwind-vue-layout')

@isset($title)
    @section('title', $title)
@endisset
@isset($canonical)
    @section('canonical', $canonical)
@endisset
@isset($robots)
    @section('meta-robots', $robots)
@endisset
@isset($description)
    @section('meta-description', $description)
@endisset
