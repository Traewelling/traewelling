<div class="card mt-3">
    <div class="card-header">{{ __('settings.delete-account') }}</div>
    <div class="card-body">
        <button class="btn btn-block btn-outline-danger mx-0" data-toggle="modal" data-target="#deleteUserModal">
            {{ __('settings.delete-account') }}
        </button>
    </div>
</div>

@include('settings.modals.deleteUserModal')
