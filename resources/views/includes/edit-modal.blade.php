<div class="modal fade" tabindex="-1" role="dialog" id="edit-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('status.update')}}" id="status-update">
                @csrf

                <input type="hidden" name="statusId"/>

                <div class="modal-header">
                    <h4 class="modal-title">{{__('modals.editStatus-title')}}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="destination-wrapper form-floating mb-2">
                        <select name="destinationStopoverId" class="form-select" required
                                id="form-status-destination"></select>
                        <label class="form-label" for="form-status-destination">
                            {{__('exit')}}
                        </label>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-floating mb-2">
                                <input class="form-control" name="manualDeparture" id="manual_departure"
                                       type="datetime-local"
                                       placeholder="{{__('export.title.origin.time.real')}}"
                                />
                                <label for="manual_departure">
                                    {{__('export.title.origin.time.real')}}
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-floating mb-2">
                                <input class="form-control" name="manualArrival" id="manual_arrival"
                                       type="datetime-local"
                                       placeholder="{{__('export.title.destination.time.real')}}"
                                />
                                <label for="manual_arrival">
                                    {{__('export.title.destination.time.real')}}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-outline">
                        <textarea class="form-control" name="body" id="status-body" maxlength="280"
                                  placeholder="{{__('modals.editStatus-label')}}"
                                  style="min-height: 130px;"></textarea>
                        <label for="status-body" class="form-label">
                            {{__('modals.editStatus-label')}}
                        </label>
                    </div>
                    <small class="text-muted float-end"><span id="body-length">-</span>/280</small>
                    <script>
                        document.querySelector('#status-body').addEventListener('input', function (e) {
                            document.querySelector('#body-length').innerText = e.target.value.length;
                        });



                    </script>

                    <div class="mt-2">
                        @include('includes.business-dropdown')
                        @include('includes.visibility-dropdown')
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{__('menu.discard')}}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{__('modals.edit-confirm')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
