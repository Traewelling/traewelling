<div class="accordion" id="accordionAdditionalDetails">
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
                {{__('vehicle-sequence')}}
            </button>
        </h3>
        <div id="collapseCarriages" class="accordion-collapse collapse bg-dark text-white"
             aria-labelledby="headingCarriageSequence"
             data-mdb-parent="#accordionAdditionalDetails"
        >
            <div class="accordion-body">
                @foreach($vehicleSequences->groupBy('vehicle.vehicle_group_id') as $vehicleGroupId => $vehicleSequence)
                    <h4 class="fs-5">{{$vehicleSequence->first()->vehicle->vehicleGroup->name}}</h4>
                    @foreach($vehicleSequence as $carriage)
                        @if(strlen($carriage->vehicle->name) === 12 && is_numeric($carriage->vehicle->name))
                            {{substr($carriage->vehicle->name, 0, 2)}}
                            {{substr($carriage->vehicle->name, 2, 2)}}
                            {{substr($carriage->vehicle->name, 4, 1)}}
                            <u>{{substr($carriage->vehicle->name, 5, 3)}}
                                {{substr($carriage->vehicle->name, 8, 3)}}</u>
                            -
                            {{substr($carriage->vehicle->name, 11)}}
                        @else
                            {{$carriage->vehicle->name}}
                        @endif
                        <small>
                            {{$carriage->vehicle_type}}
                            @isset($carriage->order_number)
                                | {{__('carriage')}} {{$carriage->order_number}}
                            @endisset
                        </small>
                        <br/>
                    @endforeach
                    <hr/>
                @endforeach

                <small>
                    {{__('source')}}:
                    DB-Wagenreihung-API - {{__('no-guarantee')}}
                </small>
            </div>
        </div>
    </div>
</div>
