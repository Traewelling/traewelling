<div class="card mt-3">
    <div class="card-header">{{ __('settings.title-loginservices') }}</div>

    <div class="card-body">
        <table class="table table-responsive">
            <thead>
                <tr>
                    <th>{{ __('settings.service') }}</th>
                    <th></th>
                    <th>{{ __('settings.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @if ($user->socialProfile != null)
                    <tr>
                        <td>Twitter</td>
                        @if ($user->socialProfile->twitter_id != null)
                            <td>{{ __('settings.connected') }}</td>
                            <td>
                                <a href="javascript:void(0)" data-provider="twitter"
                                   class="btn btn-sm btn-outline-danger disconnect">
                                    {{ __('settings.disconnect') }}
                                </a>
                            </td>
                        @else
                            <td>{{ __('settings.notconnected') }}</td>
                            <td>
                                <a href="{{ url('/auth/redirect/twitter') }}" class="btn btn-sm btn-primary">
                                    {{ __('settings.connect') }}
                                </a>
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <td>Mastodon</td>
                        @if ($user->socialProfile->mastodon_id != null)
                            <td>{{ __('settings.connected') }}</td>
                            <td>
                                <a href="javascript:void(0)" data-provider="mastodon"
                                   class="btn btn-sm btn-outline-danger disconnect">
                                    {{ __('settings.disconnect') }}
                                </a>
                            </td>
                        @else
                            <td>{{ __('settings.notconnected') }}</td>
                            <td>
                                <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                    <div class="input-group mt-0">
                                        <input type="text" name="domain" class="form-control"
                                               placeholder="{{__('user.mastodon-instance-url')}}"
                                               aria-describedby="button-addon4"/>
                                        <div id="button-addon4" class="input-group-append">
                                            <button class="btn btn-md btn-primary m-0 px-3" type="submit">
                                                <i class="fab fa-mastodon"></i> {{ __('settings.connect') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        @endif
                    </tr>
                @else
                    <tr>
                        <td>Twitter</td>
                        <td>{{ __('settings.notconnected') }}</td>
                        <td>
                            <a href="{{ url('/auth/redirect/twitter') }}" class="btn btn-sm btn-primary">
                                {{ __('settings.connect') }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Mastodon</td>
                        <td>{{ __('settings.notconnected') }}</td>
                        <td>
                            <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                <div class="input-group mt-0">
                                    <input type="text" name="domain" class="form-control"
                                           placeholder="{{__('user.mastodon-instance-url')}}"
                                           aria-describedby="button-addon4"/>
                                    <div id="button-addon4" class="input-group-append">
                                        <button class="btn btn-md btn-primary m-0 px-3" type="submit">
                                            <i class="fab fa-mastodon"></i> {{ __('settings.connect') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>