@if($dropdown)
    <li>@endif
        <form style="display: inline;" method="POST" action="{{ $getRoute() }}">
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}"/>
            <button type="submit"
                    @class(['btn btn-sm btn-primary' => !$dropdown, 'dropdown-item' => $dropdown])
                    @unless($dropdown)
                        data-mdb-toggle="tooltip" title="{{ $getText() }}"
                    @endunless
            >
                <i class="far {{ $getIcon() }}"></i>
                @if($dropdown)
                    {{ $getText() }}
                @endif
            </button>
        </form>
        @if($dropdown)</li>
@endif
