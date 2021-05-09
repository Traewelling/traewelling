<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('settings.delete-account')}}</h5>
                <button type="button" class="close" data-mdb-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{route('account.destroy')}}">
                @csrf
                <div class="modal-body">
                    {!! __('settings.delete-account-verify', ['appname' => config('app.name')])  !!}
                    <hr/>
                    <label>
                        {!! strtr(__('messages.account.please-confirm'), [':delete' => auth()->user()->username]) !!}
                    </label>
                    <input type="text" placeholder="{{auth()->user()->username}}" required
                           name="confirmation" class="form-control"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-mdb-dismiss="modal">
                        {{ __('settings.delete-account-btn-back') }}
                    </button>
                    <button class="btn btn-red btn-sm" type="submit">
                        {{ __('settings.delete-account-btn-confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
