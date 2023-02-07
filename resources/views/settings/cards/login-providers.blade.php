<div class="card mt-3">
    <div class="card-header">{{ __('settings.title-loginservices') }}</div>

    <div class="card-body">
        <table class="table table-responsive">
            <tbody>
                <tr>
                    <td>
                        <i class="fab fa-twitter"></i>
                        Twitter <br>
                        <a href="https://blog.traewelling.de/posts/twitter-deprecation/">
                            <i class="fa fa-link"></i> {{ __('settings.twitter-deprecated') }}
                        </a>
                    </td>
                    @if (auth()->user()?->socialProfile?->twitter_id != null)
                        <td class="text-success align-middle">
                            <i class="fas fa-check"></i>
                            {{ __('settings.connected') }}
                        </td>
                        <td class="align-middle">
                            <a href="javascript:void(0)" data-provider="twitter"
                               class="btn btn-sm btn-outline-danger disconnect">
                                {{ __('settings.disconnect') }}
                            </a>
                        </td>
                    @else
                        <td class="text-danger align-middle">
                            <i class="fas fa-times"></i>
                            {{ __('settings.notconnected') }}
                        </td>
                        <td class="align-middle">
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
                               class="btn btn-sm btn-outline-danger disconnect">
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
                                        <i class="fab fa-mastodon"></i> {{ __('settings.connect') }}
                                    </button>

                                </div>
                            </form>
                        </td>
                    @endif
                </tr>
            </tbody>
        </table>
    </div>
</div>
