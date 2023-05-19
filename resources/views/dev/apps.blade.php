@extends('layouts.settings')

@section('title', 'Deine Anwendungen')

@section('additional-content-end')

@endsection

@section('content')
    <div class="row mt-3">
        <div class="col-12 mb-2">
            <a href="/api/documentation" target="_blank" class="btn btn-outline-info">
                API Docs
            </a>
            <a href="{{ route('dev.apps.create') }}" class="btn btn-outline-success float-end">
                Anwendung erstellen
            </a>
        </div>
        <div class="col-12">
            @if($apps->count() > 0)
                <table class="table table-striped table-dark">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Redirect URL</th>
                            <th scope="col">Confidential</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apps as $app)
                            <tr>
                                <td>
                                    <a href="{{route('dev.apps.edit', ['appId' => $app->id])}}" class="text-white">
                                        {{ $app->name }}
                                    </a>
                                </td>
                                <td>{{ $app->redirect }}</td>
                                <td>{{ $app->confidential() == 1 ? __('yes') : __('no') }}</td>
                                <td>
                                    {{trans_choice('active-tokens-count', $app->tokens()->count(), ['count' => $app->tokens()->count()])}}
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('dev.apps.destroy', ['appId' => $app->id]) }}" method="post"
                                          onsubmit="return confirm('Are you sure you want to delete \'{{$app->name}}\'?');"
                                    >
                                        @csrf
                                        <div class="btn-group">
                                            <a href="{{route('dev.apps.edit', ['appId' => $app->id])}}"
                                               class="btn btn-sm btn-primary"
                                            >
                                                <i class="fas fa-edit"></i>
                                                {{__('edit') }}
                                            </a>
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-times"></i>
                                                {{__('delete')}}
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                Du hast noch keine Apps angelegt
            @endif
        </div>
        <!--
                <div class="my-4">
                    <strong class="mb-0">Security</strong>
                    <p>Control security alert you will be notified.</p>
                    <div class="list-group mb-5 shadow">
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="mb-0">Unusual activity notifications</strong>
                                    <p class="text-muted mb-0">Donec in quam sed urna bibendum tincidunt quis mollis mauris.</p>
                                </div>
                                <div class="col-auto">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="alert1" checked="" />
                                        <span class="custom-control-label"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="mb-0">Unauthorized financial activity</strong>
                                    <p class="text-muted mb-0">Fusce lacinia elementum eros, sed vulputate urna eleifend nec.</p>
                                </div>
                                <div class="col-auto">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="alert2" />
                                        <span class="custom-control-label"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4" />
                    <strong class="mb-0">System</strong>
                    <p>Please enable system alert you will get.</p>
                    <div class="list-group mb-5 shadow">
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="mb-0">Notify me about new features and updates</strong>
                                    <p class="text-muted mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                                </div>
                                <div class="col-auto">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="alert3" checked="" />
                                        <span class="custom-control-label"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="mb-0">Notify me by email for latest news</strong>
                                    <p class="text-muted mb-0">Nulla et tincidunt sapien. Sed eleifend volutpat elementum.</p>
                                </div>
                                <div class="col-auto">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="alert4" checked="" />
                                        <span class="custom-control-label"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="mb-0">Notify me about tips on using account</strong>
                                    <p class="text-muted mb-0">Donec in quam sed urna bibendum tincidunt quis mollis mauris.</p>
                                </div>
                                <div class="col-auto">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="alert5" />
                                        <span class="custom-control-label"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            -->
    </div>
@endsection
