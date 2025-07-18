@php use App\Models\OperatorIdentifier; @endphp
@extends('admin.layout')

@section('title', 'Operators')

@section('content')

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Trwl-ID</th>
                                <th>WikiData</th>
                                <th>Name</th>
                                <th>Identifiers <span class="badge bg-info">Motis</span><span class="badge bg-danger">HAFAS</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @php/** @var \App\Models\Operator[] $operators */ @endphp
                            @foreach($operators as $operator)
                                <tr>
                                    <td>{{$operator->id}}</td>
                                    <td>
                                        <a href="https://www.wikidata.org/wiki/{{$operator->wikidata_id}}"
                                           target="{{$operator->wikidata_id}}">
                                            {{$operator->wikidata_id}}
                                        </a>
                                    </td>
                                    <td>{{$operator->name}}</td>
                                    <td>
                                        @php /** @var OperatorIdentifier $identifier */ @endphp
                                        @foreach($operator->identifiers as $identifier)
                                            @if($identifier->type === 'motis')
                                                <span class="badge bg-info" role="button"
                                                      onclick="copyMotisToClipboard('{{$identifier->identifier}}', '{{$identifier->name}}')">
                                                    {{$identifier->identifier}} {{$identifier->name ? '(' . $identifier->name . ')' : ''}}
                                                </span>
                                            @elseif($identifier->type === 'hafas')
                                                <span class="badge bg-danger">
                                                    {{$identifier->identifier}}
                                                </span>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <script>
                                function copyMotisToClipboard(motisId, name) {
                                    // temporary function to help maintaining https://github.com/Traewelling/transitous-wikidata-operator-matching
                                    navigator.clipboard.writeText(motisId + ',"' + name + '",');
                                    notyf.success('Copied to clipboard: ' + motisId + ',"' + name + '",');
                                }
                            </script>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card mb-2">
                <div class="card-body">
                    <h2 class="fs-4">Merge operators</h2>

                    <form id="form-operator-merge">
                        <div class="form-floating mb-2">
                            <input type="number" class="form-control" id="old-operator-id">
                            <label for="old-operator-id">Old operator ID</label>
                        </div>

                        <div class="form-floating mb-2">
                            <input type="number" class="form-control" id="new-operator-id">
                            <label for="new-operator-id">New operator ID</label>
                        </div>

                        <button type="button" class="btn btn-primary" id="btn-operator-merge">
                            Merge
                        </button>
                    </form>
                    <script>
                        document.getElementById('btn-operator-merge').addEventListener('click', function (e) {
                            e.preventDefault();

                            const oldOperatorId = document.getElementById('old-operator-id').value;
                            const newOperatorId = document.getElementById('new-operator-id').value;

                            if (oldOperatorId && newOperatorId) {
                                fetch("/api/v1/operators/" + oldOperatorId + "/merge/" + newOperatorId, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                })
                                    .then(response => {
                                        if (response.ok) {
                                            notyf.success('Operators merged successfully');
                                            document.getElementById('old-operator-id').value = '';
                                            document.getElementById('new-operator-id').value = '';
                                        } else {
                                            notyf.error('Failed to merge operators');
                                        }
                                    })
                            } else {
                                notyf.error('Please fill in both operator IDs');
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>

@endsection
