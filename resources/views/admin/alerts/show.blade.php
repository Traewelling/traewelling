@extends('admin.layout')

@section('title', 'Alerts')

@php
    /**
    * @var \App\Models\Alert|null $alert
    */
    $i18n = $alert?->translations;
    if (!$i18n || $i18n->isEmpty()) {
        $i18n = collect([
            'de' => new \App\Models\AlertTranslation(),
            'en' => new \App\Models\AlertTranslation(),
        ]);
    }
    $de = $i18n->where('locale', 'de')->first();
    $en = $i18n->where('locale', 'en')->first();

@endphp
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Create Alert</h5>

                <form method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="info" {{ $alert?->type === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="success" {{ $alert?->type === 'success' ? 'selected' : '' }}>Success</option>
                            <option value="warning" {{ $alert?->type === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="danger" {{ $alert?->type === 'danger' ? 'selected' : '' }}>Danger</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="active_from" class="form-label">Active From</label>
                        <input type="date" class="form-control" id="active_from" name="active_from"
                               value="{{ $alert?->active_from?->format('Y-m-d') ?? now()->format('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label for="active_until" class="form-label">Active Until</label>
                        <input type="date" class="form-control" id="active_until" name="active_until"
                               value="{{ $alert?->active_until?->format('Y-m-d')}}">
                    </div>

                    <div class="mb-3">
                        <label for="title_de" class="form-label">Title (DE)</label>
                        <input type="text" class="form-control" id="title_de" name="title_de" value="{{ $de?->title }}">
                    </div>

                    <div class="mb-3">
                        <label for="title_en" class="form-label">Title (EN)</label>
                        <input type="text" class="form-control" id="title_en" name="title_en" value="{{ $en?->title }}">
                    </div>

                    <div class="mb-3">
                        <label for="content_de" class="form-label">Content (DE)</label>
                        <textarea class="form-control" id="content_de" name="content_de"
                                  rows="3">{{ $de?->content }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="content_en" class="form-label">Content (EN)</label>
                        <textarea class="form-control" id="content_en" name="content_en"
                                  rows="3">{{ $en?->content }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="url_de" class="form-label">URL (DE)</label>
                        <input type="url" class="form-control" id="url_de" name="url_de" value="{{ $de?->url }}">
                    </div>

                    <div class="mb-3">
                        <label for="url_en" class="form-label">URL (EN)</label>
                        <input type="url" class="form-control" id="url_en" name="url_en" value="{{ $en?->url }}">
                    </div>


                    <div class="mb-3">
                        <label for="url" class="form-label">URL (Default)</label>
                        <input type="url" class="form-control" id="url" name="url" value="{{ $alert?->url }}">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {{ $alert ? 'Update' : 'Create' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
