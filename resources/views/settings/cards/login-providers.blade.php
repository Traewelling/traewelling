<div class="card mt-3">
    <div class="card-header">{{ __('settings.title-loginservices') }}</div>

    <div class="card-body">
        <table class="table table-responsive">
            <tbody>
                <tr>
                    <td>
                        <i class="fab fa-twitter"></i>
                        Twitter
                    </td>
                    @if (auth()->user()?->socialProfile?->twitter_id != null)
                        <td class="text-success">
                            <i class="fas fa-check"></i>
                            {{ __('settings.connected') }}
                        </td>
                        <td>
                            <a href="javascript:void(0)" data-provider="twitter"
                               class="btn btn-sm btn-block btn-outline-danger disconnect">
                                {{ __('settings.disconnect') }}
                            </a>
                        </td>
                    @else
                        <td class="text-danger">
                            <i class="fas fa-times"></i>
                            {{ __('settings.notconnected') }}
                        </td>
                        <td>
                            <a href="{{ url('/auth/redirect/twitter') }}" class="btn btn-sm btn-block btn-primary">
                                <i class="fa-solid fa-link"></i>
                                {{ __('settings.connect') }}
                            </a>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td>
                        <i class="fab fa-mastodon"></i>
                        Mastodon
                    </td>
                    @if (auth()->user()?->socialProfile?->mastodon_id != null)
                        <td class="text-success">
                            <i class="fas fa-check"></i>
                            {{ __('settings.connected') }}
                        </td>
                        <td>
                            <a href="javascript:void(0)" data-provider="mastodon"
                               class="btn btn-sm btn-block  btn-outline-danger disconnect">
                                {{ __('settings.disconnect') }}
                            </a>
                        </td>
                    @else
                        <td class="text-danger">
                            <i class="fas fa-times"></i>
                            {{ __('settings.notconnected') }}
                        </td>
                        <td>
                            <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                                <div class="input-group mt-0">
                                    <input type="text" name="domain" class="form-control"
                                           placeholder="{{__('user.mastodon-instance-url')}}"
                                           aria-describedby="button-addon4"/>
                                    <button class="btn btn-md btn-primary m-0 px-3" type="submit">
                                        <i class="fab fa-mastodon"></i>
                                        {{ __('settings.connect') }}
                                    </button>
                                </div>
                            </form>
                        </td>
                    @endif
                </tr>

                @foreach($tokens as $token)
                    <tr>
                        <td>
                            <i class="fa-solid fa-code"></i>
                            {{ $token->client->name === 'Träwelling Personal Access Client' ? __('unknown-service') : $token->client->name }}
                        </td>
                        <td>
                            <span class="text-success">
                                <i class="fas fa-check"></i>
                                {{ __('settings.connected') }}
                            </span>
                            <br />
                            <small>
                                {{__('access-expires-at', ['diffForHumans' => $token->expires_at->diffForHumans()])}}
                            </small>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('deltoken') }}">
                                @csrf
                                <input type="hidden" name="tokenId" value="{{$token->id}}"/>
                                <button class="btn btn-sm btn-block btn-outline-danger mx-0">
                                    <i class="fa-solid fa-link-slash"></i>
                                    {{ __('settings.disconnect') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
