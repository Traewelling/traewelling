@extends('admin.layout')

@section('title', isset($event) ? 'Edit Event' : 'Create Event')
@php($event ??= null)

@section('content')
    <form id="event-form" data-event-id="{{ $event?->id }}">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.name') }}<span class="text-danger">*</span>:
                            </label>
                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control" name="name" required
                                       value="{{ $event?->name }}"/>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="hashtag" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.hashtag') }}:
                            </label>
                            <div class="col-md-8">
                                <input id="hashtag" type="text" class="form-control" name="hashtag"
                                       value="{{ $event?->hashtag }}"/>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="host" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.host') }}:
                            </label>
                            <div class="col-md-8">
                                <input id="host" type="text" class="form-control" name="host"
                                       value="{{ $event?->host }}"/>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="url" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.url') }}:
                            </label>
                            <div class="col-md-8">
                                <input id="url" type="url" class="form-control" name="url"
                                       value="{{ $event?->url }}"/>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="nearest_station_name" class="col-md-4 col-form-label text-md-right">
                                {{ __('events.closestStation') }}:
                            </label>
                            <div class="col-md-8">
                                <input id="nearest_station_name" type="text" name="nearest_station_name"
                                       class="form-control"
                                       placeholder="{{ __('stationboard.station-placeholder') }}"
                                       value="{{ $event?->nearest_station_name ?? $event?->station?->name }}"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="fs-5">
                            Checkin between<span class="text-danger">*</span>:
                        </h2>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="checkin_start" type="datetime-local" class="form-control"
                                           name="checkin_start" required
                                           value="{{ optional($event)->checkin_start }}"/>
                                    <label for="checkin_start">Checkin {{ __('events.begin') }}:</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="checkin_end" type="datetime-local" class="form-control"
                                           name="checkin_end" required
                                           value="{{ optional($event)->checkin_end }}"/>
                                    <label for="checkin_end">{{ __('events.end') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="fs-5">
                            Event-Time (optional):
                        </h2>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="event_start" type="datetime-local" class="form-control"
                                           name="event_start"
                                           value="{{ optional($event)->event_start }}"/>
                                    <label for="event_start">{{ __('events.begin') }}</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input id="event_end" type="datetime-local" class="form-control"
                                           name="event_end"
                                           value="{{ optional($event)->event_end }}"/>
                                    <label for="event_end">{{ __('events.end') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body text-end">
                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('event-form');
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.addEventListener('submit', async e => {
                e.preventDefault();

                const payload = {
                    name: document.getElementById('name').value,
                    hashtag: document.getElementById('hashtag').value,
                    host: document.getElementById('host').value,
                    url: document.getElementById('url').value,
                    nearest_station_name: document.getElementById('station-autocomplete').value,
                    checkin_start: document.getElementById('checkin_start').value,
                    checkin_end: document.getElementById('checkin_end').value,
                    event_start: document.getElementById('event_start').value,
                    event_end: document.getElementById('event_end').value,
                };

                const eventId = form.dataset.eventId;
                const apiUrl = eventId
                    ? `/api/v1/events/${eventId}`
                    : `/api/v1/events`;
                const method = eventId ? 'PUT' : 'POST';

                try {
                    const res = await fetch(apiUrl, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!res.ok) {
                        const err = await res.json();
                        console.error('Validation failed:', err);
                        return;
                    }

                    const data = await res.json();
                    window.location.href = '/admin/events';
                } catch (error) {
                    console.error('API-Request error:', error);
                }
            });
        });
    </script>
@endsection
