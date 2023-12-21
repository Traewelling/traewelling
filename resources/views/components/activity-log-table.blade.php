<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Causer</th>
                <th>Action</th>
                <th>Model</th>
                <th>Changes</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
                <tr>
                    <td>
                        @if($activity?->causer instanceof \App\Models\User)
                            <b>User</b><br/>
                            {{$activity?->causer?->name}}
                        @endif
                    </td>
                    <td>{{__($activity->description)}}</td>
                    <td>
                        @if($activity->subject_type)
                            {{__(class_basename($activity->subject_type))}} {{$activity->subject_id}}
                        @endif
                    </td>
                    <td>
                        @if(isset($activity->changes['old']))
                            @foreach($activity->changes['old'] as $name => $val)
                                <b>{{__(class_basename($activity->subject_type) . '.' . $name)}}</b>:
                                <span class="text-secondary">"{{$val}}"</span>
                                <i class="fa-solid fa-arrow-right"></i>
                                <span
                                    class="text-info">"{{$activity->changes['attributes'][$name] ?? '???'}}"</span>
                                <br/>
                            @endforeach
                        @elseif(isset($activity->changes['attributes']))
                            @foreach($activity->changes['attributes'] as $name => $val)
                                @if($val == '')
                                    @continue
                                @endif
                                <b>{{__(class_basename($activity->subject_type) . '.' . $name)}}</b>:
                                <span
                                    class="{{$activity->description === 'created' ? 'text-success' : 'text-danger'}}">
                                                        "{{$val}}"
                                                    </span>
                                <br/>
                            @endforeach
                        @endif
                    </td>
                    <td>{{$activity->created_at->format('d.m.Y H:i')}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
