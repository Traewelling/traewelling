@if($activities->isEmpty())
    <p class="text-muted text-center">
        <i class="fa fa-info-circle"></i>
        No activities found.
    </p>
@else
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Causer</th>
                    <th>Description</th>
                    <th>Object</th>
                    <th>Attributes</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities as $activity)
                    <tr>
                        <td>
                            @if($activity?->causer instanceof \App\Models\User)
                                {{$activity?->causer?->name}}
                            @endif
                        </td>
                        <td>{{$activity->description}}</td>
                        <td>
                            @if($activity->subject_type)
                                <a href="{{route('admin.activity', ['subject_type' => $activity->subject_type, 'subject_id' => $activity->subject_id])}}">
                                    {{class_basename($activity->subject_type)}} {{$activity->subject_id}}
                                </a>
                            @endif
                        </td>
                        <td>
                            @if(isset($activity->changes['old']))
                                @foreach($activity->changes['old'] as $name => $val)
                                    <b>{{class_basename($activity->subject) . '.' . $name}}</b>:
                                    <span class="text-secondary">"{{$val}}"</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                    <span class="text-info">"{{$activity->changes['attributes'][$name] ?? '???'}}"</span>
                                    <br/>
                                @endforeach
                            @elseif(isset($activity->changes['attributes']))
                                @foreach($activity->changes['attributes'] as $name => $val)
                                    @if($val == '')
                                        @continue
                                    @endif
                                    <b>{{class_basename($activity->subject) . '.' . $name}}</b>:
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
@endif
