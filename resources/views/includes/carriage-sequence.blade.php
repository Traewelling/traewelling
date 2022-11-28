<div class="accordion mt-3" id="accordionAdditionalDetails">
    <div class="accordion-item">
        <h3 class="accordion-header" id="headingCarriageSequence">
            <button
                class="accordion-button collapsed text-black"
                type="button"
                data-mdb-toggle="collapse"
                data-mdb-target="#collapseCarriages"
                aria-expanded="false"
                aria-controls="collapseCarriages"
            >
                <i class="fa-solid fa-sort me-2"></i>
                {{__('carriage-sequence')}}
            </button>
        </h3>
        <div id="collapseCarriages" class="accordion-collapse collapse bg-white"
             aria-labelledby="headingCarriageSequence"
             data-mdb-parent="#accordionAdditionalDetails"
        >
            <div class="accordion-body">
                @foreach($carriageSequence as $vehicle)
                    @if(strlen($vehicle->vehicle_number) === 12)
                        {{substr($vehicle->vehicle_number, 0, 2)}}
                        {{substr($vehicle->vehicle_number, 2, 2)}}
                        {{substr($vehicle->vehicle_number, 4, 1)}}
                        <u>
                            {{substr($vehicle->vehicle_number, 5, 3)}}
                            {{substr($vehicle->vehicle_number, 8, 3)}}</u>-{{substr($vehicle->vehicle_number, 11)}}
                    @else
                        {{$vehicle->vehicle_number}}
                    @endif
                    <small>
                        {{$vehicle->vehicle_type}}
                        @isset($vehicle->order_number)
                            | {{__('carriage')}} {{$vehicle->order_number}}
                        @endisset
                    </small>
                    <br/>
                @endforeach
            </div>
        </div>
    </div>
</div>
