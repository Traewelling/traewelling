@php
    use App\Enum\Report\ReportableSubject;
    use App\Enum\Report\ReportReason;
@endphp

@extends('layouts.app')

@section('title', __('report-something'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <h1>{{__('report-something')}}</h1>

                <form id="report">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-2 {{request()->has('subjectType') ? 'd-none' : ''}}">
                                <select name="subjectType" class="form-select" required id="subjectType">
                                    <option value=""
                                            {{!request()->has('subjectType') ? 'selected' : ''}} disabled></option>
                                    @foreach(ReportableSubject::cases() as $reportableSubject)
                                        <option value="{{$reportableSubject->value}}"
                                            {{request()->get('subjectType') === $reportableSubject->value ? 'selected' : ''}}
                                        >
                                            {{__('report-subject.' . $reportableSubject->value)}}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="subjectType">{{__('report.subjectType')}}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-2 {{request()->has('subjectId') ? 'd-none' : ''}}">
                                <input type="number" name="subjectId" id="subjectId" class="form-control" required
                                       value="{{request()->get('subjectId')}}"
                                />
                                <label for="subjectId">{{__('report.subjectId')}}</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-2">
                        <select name="reason" class="form-select" id="reason" required>
                            <option value="" selected disabled></option>
                            @foreach(ReportReason::cases() as $reason)
                                <option value="{{$reason->value}}">
                                    {{__('report-reason.' . $reason->value)}}
                                </option>
                            @endforeach
                        </select>
                        <label for="title">{{__('report.reason')}}</label>
                    </div>

                    <div class="form-floating mb-2">
                        <textarea name="description" id="description" class="form-control" required
                                  style="min-height: 100px;"></textarea>
                        <label for="description">{{__('report.description')}}</label>
                    </div>
                    <div class="form-text text-danger mb-2 d-none" id="error-text">
                        {{__('report.min-length')}}
                    </div>

                    <button class="btn btn-sm btn-outline-primary" type="submit">
                        {{__('report.submit')}}
                    </button>

                </form>

                <script>
                    // seeing this quick and dirty code?
                    // maybe you want to help us out and make it better?
                    // Create a Pull Request on GitHub!
                    // https://github.com/Traewelling/traewelling
                    const description = document.getElementById('description');
                    const errorText = document.getElementById('error-text');
                    description.addEventListener('focusout', (event) => {
                        if (event.target.value.length > 10) {
                            event.target.classList.remove('is-invalid');
                            errorText.classList.add('d-none');
                        } else {
                            event.target.classList.add('is-invalid');
                            errorText.classList.remove('d-none');
                        }
                    })

                    document.getElementById('report').addEventListener('submit', function (event) {
                        event.preventDefault();
                        if (description.value.length < 10) {
                            description.classList.add('is-invalid');
                            errorText.classList.remove('d-none');
                            return;
                        }

                        let formData = new FormData(this);

                        fetch('/api/v1/report', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => {
                                if (response.ok) {
                                    notyf.success('{{__('report.success')}}');
                                    document.getElementById('report').reset();
                                    return response;
                                }

                                notyf.error('{{__('report.error')}}');
                                return response;
                            });
                    });
                </script>

            </div>
        </div>
    </div>
@endsection
