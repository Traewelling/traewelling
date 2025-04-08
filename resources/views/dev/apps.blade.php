@extends('layouts.settings')
@section('title', __('your-apps'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card mb-3 my-0">
                <div class="card-header">
                    <a href="/api/documentation" target="_blank" class="btn btn-outline-info">
                        API Docs
                    </a>
                    <a href="{{ route('dev.apps.create') }}" class="btn btn-outline-success float-end">
                        {{__('create-app')}}
                    </a>
                </div>

                <div class="card-body px-0">
                    @if($apps->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
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
                                                <a href="{{route('dev.apps.edit', ['appId' => $app->id])}}">
                                                    {{ $app->name }}
                                                </a>
                                            </td>
                                            <td>{{ $app->redirect }}</td>
                                            <td>{{ $app->confidential() == 1 ? __('yes') : __('no') }}</td>
                                            <td>
                                                {{trans_choice('active-tokens-count', $app->tokens()->count(), ['count' => $app->tokens()->count()])}}
                                            </td>
                                            <td class="text-end">
                                                <form action="{{ route('dev.apps.destroy', ['appId' => $app->id]) }}"
                                                      method="post"
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
                        </div>
                    @else
                        <p class="text-danger text-center">
                            <i class="fa-regular fa-face-frown-open"></i>
                            {{__('no-own-apps')}}
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-5">
            @include('dev.access-token')
        </div>
    </div>
@endsection
