<div class="modal fade" tabindex="-1" role="dialog" id="modal-status-delete">
    <input type="hidden" name="statusId"/>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__('modals.deleteStatus-title')}}</h4>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-mdb-dismiss="modal">
                    {{__('menu.abort')}}
                </button>
                <button type="button" class="btn btn-danger"
                        data-mdb-dismiss="modal"
                        onclick="Status.destroy(document.querySelector('#modal-status-delete input[name=\'statusId\']').value)"
                >
                    {{__('modals.delete-confirm')}}
                </button>
            </div>
        </div>
    </div>
</div>
