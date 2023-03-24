<div class="modal fade" id="mastodon-auth" tabindex="-1" role="dialog"
     aria-hidden="true" aria-labelledby="mastodon-auth-title">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="GET" action="{{ url('/auth/redirect/mastodon') }}">
                <div class="modal-body" id="notifications-list">
                    <div class="form-outline">
                        <input type="text" name="domain" class="form-control" required
                               aria-describedby="button-addon4">
                        <label class="form-label" for="domain">{{__('user.mastodon-instance-url')}}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-mdb-dismiss="modal">
                        {{__('menu.discard')}}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fab fa-mastodon"></i> {{__('user.login')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
