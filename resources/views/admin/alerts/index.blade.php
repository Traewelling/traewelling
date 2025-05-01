@extends('admin.layout')

@section('title', 'Alerts')

@section('content')

    @php
        /**
        * @var \App\Models\Alert[] $Alerts
        */
    @endphp
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Alerts</h5>

                    <a href="{{ route('admin.alerts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Alert
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" aria-labelledby="pageTitle">
                        <thead>
                        <tr>
                            <th>Active</th>
                            <th>Type</th>
                            <th>DE</th>
                            <th>EN</th>
                            <th>Active From</th>
                            <th>Active Until</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($alerts as $alert)
                            @php
                                $langs = $alert->translations;
                                $de = $langs->where('locale', 'de')->first();
                                $en = $langs->where('locale', 'en')->first();
                                $now = now()->startOfDay();
                                $active = $alert->active_from <= $now && ($alert->active_until >= $now || $alert->active_until == null);
                            @endphp
                            <tr>
                                <td>
                                    @if($active)
                                        <div class="spinner-grow text-success" style="width: 1rem; height: 1rem;"></div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $alert->type }}">
                                      {{ $alert->type }}
                                    </span>
                                </td>
                                <td>
                                    <abbr title="{{ $de?->description }}">
                                        {{ $de?->title }}
                                    </abbr>
                                </td>
                                <td>
                                    <abbr title="{{ $en?->description }}">
                                        {{ $en?->title }}
                                    </abbr>
                                </td>
                                <td>{{ $alert->active_from->toDateString() }}</td>
                                <td>{{ $alert->active_until?->toDateString() }}</td>
                                <td>
                                    <a href="{{ route('admin.alerts.edit', $alert->id) }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.alerts.destroy', $alert->id) }}" method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

@endsection
