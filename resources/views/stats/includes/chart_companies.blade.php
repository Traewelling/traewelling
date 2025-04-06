<div class="card">
    <div class="card-body">
        <h5>{{__('stats.companies')}}</h5>
        @if($topOperators->count() > 0)
            <div id="chart_companies"></div>
        @else
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('footer')
    @parent
    @if($topOperators->count() > 0)
        <script>
            let chartCompaniesSeries = [
                @foreach($topOperators as $operator)
                    {{$operator->duration}},
                @endforeach
            ];
            let chartCompaniesLabels = [
                @foreach($topOperators as $operator)
                    '{{$operator->name ?? __('other')}}',
                @endforeach
            ];
            let chartCompaniesMinutes = "{{ __ ('time.minutes') }}";
        </script>
    @endif
@endsection
