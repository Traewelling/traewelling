<div class="modal fade bd-example-modal-lg" id="notifications-board" tabindex="-1" role="dialog"
     aria-hidden="true" aria-labelledby="notifications-board-title">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-4" id="notifications-board-title">
                    {{ __('notifications.title') }}
                </h2>
                <button type="button" class="" id="mark-all-read"
                        aria-label="{{ __('notifications.mark-all-read') }}">
                    <span aria-hidden="true"><i class="fas fa-check-double"></i></span>
                </button>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notifications-list">
                <div id="notifications-empty" class="text-center text-muted">
                    {{ __('notifications.empty') }}
                    <br/>¯\_(ツ)_/¯
                </div>
            </div>
        </div>
    </div>
</div>
