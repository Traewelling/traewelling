@extends('layouts.app')

@section('title', 'Motis sources')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    This page is for debugging purposes only.
                    It's showing raw data used by this Träwelling instance.
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        @php
                            /** @var \App\Models\MotisSourceLicense[] $sources */
                        @endphp
                        @foreach($sources as $source)
                            <tr>
                                <td>{{$source->country}}</td>
                                <td>
                                    {{$source->human_name}}
                                    <br>
                                    <small>
                                        <a href="{{$source->source_url}}" target="_blank">
                                            {{$source->name}}
                                        </a>
                                    </small>
                                </td>
                                <td>
                                    @if($source->manualLicense)
                                        <a href="{{$source->manualLicense->license_url}}" target="_blank">
                                            {{$source->manualLicense->human_name}}
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        @if($source->attribution_text)
                                            <br>
                                            <small class="text-muted">{{$source->attribution_text}}</small>
                                        @endif
                                    @elseif($source->spdx)
                                        @if($source->license_url)
                                            <a href="{{$source->license_url}}" target="_blank">
                                                {{$source->spdx}}
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @else
                                            {{$source->spdx}}
                                        @endif
                                        @if($source->attribution_text)
                                            <br>
                                            <small class="text-muted">{{$source->attribution_text}}</small>
                                        @endif
                                    @elseif($source->attribution_text)
                                        <span class="badge bg-info text-dark">Custom</span>
                                        <br>
                                        <small class="text-muted">{{$source->attribution_text}}</small>
                                    @else
                                        <span class="badge bg-secondary">No license information</span>
                                    @endif
                                </td>
                                <td>
                                    @if($source->force_active)
                                        <span class="badge bg-success">Forced Active</span>
                                    @elseif($source->active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
