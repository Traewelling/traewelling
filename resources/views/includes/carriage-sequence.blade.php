<div class="card mt-2">
    <div class="card-body">
        <h3 class="fs-5">
            <i class="fa-solid fa-sort"></i>
            Wagenreihung
        </h3>
        @foreach($carriageSequence as $vehicle)
            {{$vehicle->vehicle_number}}
            {{$vehicle->vehicle_type}}
            <br/>
        @endforeach
    </div>
</div>


