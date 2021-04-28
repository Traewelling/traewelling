<div class="modal fade" tabindex="-1" role="dialog" id="edit-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__('modals.editStatus-title')}}</h4>
                <button type="button" class="close" data-mdb-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="status-body">{{__('modals.editStatus-label')}}</label>
                        <textarea class="form-control" name="status-body" id="status-body" rows="5"></textarea>
                    </div>
                    <div class="mt-2">
                        <input type="hidden" id="business_check" name="business_check" class="form-control" value="0"/>
                        <label class="btn btn-sm btn-outline-twitter dropdown-toggle"
                               type="button"
                               id="dropdownMenuButton"
                               data-mdb-toggle="dropdown"
                               aria-expanded="false"
                        >
                            <i class="fa fa-user" id="business-user"></i>
                            <i class="fa fa-briefcase d-none" id="business-briefcase"></i>
                            <i class="fa fa-building d-none" id="business-building"></i>
                        </label>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li class="dropdown-item" id="business-li-user"><i
                                        class="fa fa-user"></i> {{ __('stationboard.business.private') }}</li>
                            <li class="dropdown-item" id="business-li-briefcase"><i
                                        class="fa fa-briefcase"></i> {{ __('stationboard.business.business') }}
                                <br/> <span
                                        class="text-muted"> {{ __('stationboard.business.business.detail') }}</span>
                            </li>
                            <li class="dropdown-item" id="business-li-building"><i
                                        class="fa fa-building"></i> {{ __('stationboard.business.commute') }} <br/>
                                <span class="text-muted"> {{ __('stationboard.business.commute.detail') }}</span>
                            </li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-mdb-dismiss="modal">{{__('menu.discard')}}</button>
                <button type="button" class="btn btn-primary" id="modal-save">{{__('modals.edit-confirm')}}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
