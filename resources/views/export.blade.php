@extends('layouts.app')

@section('title', __('export.title'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1><i class="fa fa-save"></i> {{__('export.title')}}</h1>
                <p class="lead">{{__('export.lead')}}</p>
            </div>

            <div class="col-md-8">
                <form method="POST" action="/api/v1/export/statuses">
                    @csrf
                    <div class="card mb-2">
                        <div class="card-body">
                            <h2 class="fs-5">
                                <i class="fa-regular fa-file-code"></i>&nbsp;
                                {{__('export.submit')}} PDF / CSV
                            </h2>

                            <hr/>

                            <p class="fw-bold mb-1">
                                <i class="fa-solid fa-table-list"></i>
                                {{__('export.columns')}}
                            </p>

                            <p class="fst-italic mb-1 text-center">
                                {{__('export.predefined')}}...
                            </p>
                            <div class="row mb-1 text-center">
                                <div class="col">
                                    <div class="form-floating">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="export-nominal">
                                            {{ __('export.nominal') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="export-tags">
                                            {{ __('export.nominal-tags') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="export-all">
                                            {{ __('export.all') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="fst-italic mb-1 text-center">
                                ...{{__('export.or-choose')}}:
                            </p>

                            <select id="export-select" class="form-select" size="12" multiple name="columns[]" required>
                                @foreach(\App\Enum\ExportableColumn::cases() as $column)
                                    <option value="{{$column->value}}">
                                        {{$column->title()}}
                                    </option>
                                @endforeach
                            </select>

                            <script>
                                document.querySelector('select[name="columns[]"]')
                                    .addEventListener('change', function (e) {
                                        if (e.target.selectedOptions.length > 7) {
                                            document.querySelector('#alert-pdf-count').classList.remove('d-none');
                                        } else {
                                            document.querySelector('#alert-pdf-count').classList.add('d-none');
                                        }
                                    });
                            </script>

                            <div class="alert alert-warning mt-3 d-none" role="alert" id="alert-pdf-count">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                {{__('export.pdf.many')}}
                            </div>

                            <hr/>
                            <p class="fw-bold mb-1">
                                <i class="fa-regular fa-calendar-days"></i>
                                {{__('export.period')}}
                            </p>

                            <div class="row">
                                <div class="col">
                                    <div class="form-floating">
                                        <input name="from" id="from" type="date"
                                               value="{{now()->firstOfMonth()->format('Y-m-d')}}"
                                               class="form-control"/>
                                        <label for="from">{{__('export.begin')}}</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                        <input name="until" id="until" type="date"
                                               value="{{now()->lastOfMonth()->format('Y-m-d')}}"
                                               class="form-control"/>
                                        <label for="until">{{__('export.end')}}</label>
                                    </div>
                                </div>
                            </div>

                            <hr/>
                            <p class="fw-bold mb-1">
                                <i class="fa-solid fa-download"></i>
                                {{__('export.format')}}
                            </p>

                            <div class="row pt-2">
                                <div class="col text-end">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary" name="filetype" value="pdf">
                                            <i class="fa-regular fa-file-pdf"></i>&nbsp;&nbsp;PDF
                                        </button>
                                        <button type="submit" class="btn btn-primary" name="filetype" value="csv_human">
                                            <i class="fa-solid fa-file-csv"></i>&nbsp;&nbsp;CSV
                                            ({{__('human-readable-headings')}})
                                        </button>
                                        <button type="submit" class="btn btn-primary" name="filetype"
                                                value="csv_machine">
                                            <i class="fa-solid fa-file-csv"></i>&nbsp;&nbsp;CSV
                                            ({{__('machine-readable-headings')}})
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-4">
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-5">
                            <i class="fa-regular fa-file-code"></i>&nbsp;
                            {{__('export.submit')}} JSON
                        </h2>

                        {{__('export.json.description')}}
                        {{__('export.json.description2')}}
                        {{__('export.json.description3')}}

                        <hr/>

                        <form method="POST" action="/api/v1/export/statuses">
                            @csrf
                            <div class="row">
                                <div class="col">
                                    <div class="form-floating">
                                        <input name="from" id="from" type="date"
                                               value="{{now()->firstOfMonth()->format('Y-m-d')}}"
                                               class="form-control"/>
                                        <label for="from">{{__('export.begin')}}</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                        <input name="until" id="until" type="date"
                                               value="{{now()->lastOfMonth()->format('Y-m-d')}}"
                                               class="form-control"/>
                                        <label for="until">{{__('export.end')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="col text-end">
                                    <button type="submit" class="btn btn-primary" name="filetype" value="json">
                                        <i class="fa-solid fa-download"></i>
                                        {{__('export.generate')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
