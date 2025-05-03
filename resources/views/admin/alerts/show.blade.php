@extends('admin.layout')

@section('title', $alert ? 'Edit Alert' : 'Create Alert')

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
                <form id="alert-form"
                      data-endpoint="{{ $alert ? url('/api/v1/alerts/' . $alert->id) : url('/api/v1/alerts') }}"
                      data-method="{{ $alert ? 'PUT' : 'POST' }}">

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="type" name="type">
                                    @foreach(['info','success','warning','danger'] as $type)
                                        <option value="{{ $type }}" {{ $alert?->type === $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="type">Type</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="active_from" name="active_from"
                                       value="{{ $alert?->active_from?->format('Y-m-d') ?? now()->format('Y-m-d') }}">
                                <label for="active_from">Active From</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="active_until" name="active_until"
                                       value="{{ $alert?->active_until?->format('Y-m-d') }}">
                                <label for="active_until">Active Until</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">German</h6>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="title_de" name="title_de"
                                       value="{{ $de?->title }}" placeholder="Title">
                                <label for="title_de">Title (DE)</label>
                            </div>
                            <div class="form-floating mb-3">
                            <textarea class="form-control" id="content_de" name="content_de" rows="3"
                                      placeholder="Content">{{ $de?->content }}</textarea>
                                <label for="content_de">Content (DE)</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="url" class="form-control" id="url_de" name="url_de"
                                       value="{{ $de?->url }}" placeholder="URL">
                                <label for="url_de">URL (DE)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">English</h6>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="title_en" name="title_en"
                                       value="{{ $en?->title }}" placeholder="Title">
                                <label for="title_en">Title (EN)</label>
                            </div>
                            <div class="form-floating mb-3">
                            <textarea class="form-control" id="content_en" name="content_en" rows="3"
                                      placeholder="Content">{{ $en?->content }}</textarea>
                                <label for="content_en">Content (EN)</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="url" class="form-control" id="url_en" name="url_en"
                                       value="{{ $en?->url }}" placeholder="URL">
                                <label for="url_en">URL (EN)</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="url" class="form-control" id="url" name="url"
                               value="{{ $alert?->url }}" placeholder="Default URL">
                        <label for="url">URL (Default)</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {{ $alert ? 'Update Alert' : 'Create Alert' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('alert-form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const endpoint = form.dataset.endpoint;
                const method = form.dataset.method;
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const payload = {
                    type: document.getElementById('type').value,
                    active_from: document.getElementById('active_from').value,
                    active_until: document.getElementById('active_until').value || null,
                    title_de: document.getElementById('title_de').value,
                    content_de: document.getElementById('content_de').value,
                    url_de: document.getElementById('url_de').value,
                    title_en: document.getElementById('title_en').value,
                    content_en: document.getElementById('content_en').value,
                    url_en: document.getElementById('url_en').value,
                    url: document.getElementById('url').value,
                };

                fetch(endpoint, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                })
                    .then(response => {
                        if (response.ok) {
                            window.location.href = '/admin/alerts';
                        } else {
                            return response.json().then(err => Promise.reject(err));
                        }
                    })
                    .catch(error => {
                        console.error('Save failed:', error);
                        alert('Failed to {{ $alert ? 'update' : 'create' }} the alert. Please check your input.');
                    });
            });
        });
    </script>

@endsection
