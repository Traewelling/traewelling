@if($isDropdown())
    <li>@endif
        <form style="display: inline;" method="POST" action="{{ $getRoute() }}">
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}"/>
            <button type="submit"
                    @class(['btn btn-sm btn-primary' => !$dropdown, 'dropdown-item' => $dropdown])
                    @unless($showText())
                        data-mdb-toggle="tooltip" title="{{ $getText() }}"
                    @endunless
                    @disabled($isDisabled())
            >
                <div class="dropdown-icon-suspense">
                    <i class="fas {{ $getIcon() }}"></i>
                </div>
                @if($showText())
                    {{ $getText() }}
                @endif
            </button>
        </form>
        @if($isDropdown())</li>
@endif
