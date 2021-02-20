<div class="card mt-3">
    <div class="card-header">{{ __('settings.delete-account') }}</div>
    <div class="card-body">
        <button class="btn btn-block btn-outline-danger mx-0" data-toggle="modal" data-target="#deleteUserModal">
            {{ __('settings.delete-account') }}
        </button>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('settings.delete-account')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {!! __('settings.delete-account-verify', ['appname' => config('app.name')])  !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-grey btn-sm" data-dismiss="modal">
                    {{ __('settings.delete-account-btn-back') }}
                </button>
                <a href="{{ route('account.destroy') }}" role="button" class="btn btn-red btn-sm">
                    {{ __('settings.delete-account-btn-confirm') }}
                </a>
            </div>
        </div>
    </div>
</div>