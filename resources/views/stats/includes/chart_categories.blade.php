<div class="card">
    <div class="card-body">
        <h5>{{__('stats.categories')}}</h5>

        @if($topCategories->count() > 0)
            <div id="chart_favourite_types" style="height: 200px;"></div>
        @else
            <p class="text-danger font-weight-bold mt-2">{{__('stats.no-data')}}</p>
        @endif
    </div>
</div>

@section('footer')
    @parent
    @if($topCategories->count() > 0)
        TEST
        <script>
            let categorySeries = [
                @foreach($topCategories as $category)
                    {{$category->duration}},
                @endforeach
            ];
            let categoryLabels = [
                @foreach($topCategories as $category)
                    '{{$category->name}}',
                @endforeach
            ];
            let categoryMinutes = "{{ __('time.minutes') }}";
        </script>
    @endif
@endsection
