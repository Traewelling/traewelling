<div class="modal fade" tabindex="-1" role="dialog" id="edit-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__('modals.editStatus-title')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="status-body">{{__('modals.editStatus-label')}}</label>
                        <textarea class="form-control" name="status-body" id="status-body" rows="5"></textarea>
                    </div>
                    <div class="mt-2">
                        @include('includes.business-dropdown')
                        @include('includes.visibility-dropdown')
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-mdb-dismiss="modal">{{__('menu.discard')}}</button>
                <button type="button" class="btn btn-primary"
                        id="modal-trwl-edit-save">{{__('modals.edit-confirm')}}</button>
            </div>
        </div>
    </div>
</div>
