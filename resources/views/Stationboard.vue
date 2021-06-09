<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">
        <StationForm></StationForm>
        <div id="timepicker-wrapper">
          <div class="text-center">
            <div class="btn-group" role="group">
              <a
                 :title="i18n.get('_.stationboard.minus-15')"
                 class="btn btn-light">
                <i class="fas fa-arrow-circle-left" aria-hidden="true"></i>
              </a>
              <a href="#" id="timepicker-reveal" :title="i18n.get('_.stationboard.dt-picker')"
                 class="btn btn-light btn-rounded">
                <i class="fas fa-clock" aria-hidden="true"></i>
              </a>
              <a
                 :title="i18n.get('_.stationboard.plus-15')"
                 class="btn btn-light">
                <i class="fas fa-arrow-circle-right" aria-hidden="true"></i>
              </a>
            </div>
          </div>
          <div class="text-center mt-4">
            <form class="form-inline" v-if="false">
              <div class="input-group mb-3 mx-auto">
                <input type="datetime-local" class="form-control" id="timepicker" name="when"
                       aria-describedby="button-addontime"/>
                <button class="btn btn-outline-primary" type="submit" id="button-addontime">
                  {{i18n.get('_.stationboard.set-time')}}
                </button>
              </div>
            </form>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="float-end">
                <a>
                  <!-- ToDo: set home, but with modal! -->
                  <i class="fa fa-home" aria-hidden="true"></i>
                </a>
              </div>
              $station->name 
              <small>
                <i class="far fa-clock fa-sm" aria-hidden="true"></i>
                 $when->isoFormat(i18n.get('_.time-format.with-day'))
              </small>
            </div>

            <div class="card-body p-0 table-responsive">
              @if(empty($departures))
              <table class="table table-dark table-borderless m-0">
                <tr>
                  <td>{{ i18n.get('_.stationboard.no-departures') }}</td>
                </tr>
              </table>
              @else
              <table class="table table-dark table-borderless table-hover m-0">
                <thead>
                  <tr>
                    <th></th>
                    <th>{{i18n.get('_.stationboard.line')}}</th>
                    <th>{{i18n.get('_.stationboard.destination')}}</th>
                    <th>{{i18n.get('_.stationboard.dep-time')}}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($departures as $departure)
                  <tr @if(!isset($departure->cancelled)) class="trainrow"@endif
                    data-tripID="$departure->tripId"
                    data-lineName="$departure->line->name != null ? $departure->line->name : $departure->line->fahrtNr"
                    data-start="$departure->stop->id"
                    data-departure="$departure->plannedWhen">
                    <td>@if (file_exists(public_path('img/'.$departure->line->product.'.svg')))
                      <img class="product-icon"
                           alt="Icon of $departure->line->product"
                           src="asset('img/'.$departure->line->product.'.svg')">
                      @else
                      <i class="fa fa-train" aria-hidden="true"></i>
                      @endif</td>
                    <td>
                      @if($departure->line->name)
                       str_replace(" ", "&nbsp;", $departure->line->name)
                      @else
                       str_replace(" ", "&nbsp;", $departure->line->fahrtNr)
                      @endif

                    </td>
                    <td> $departure->direction </td>
                    <td>
                      @if(isset($departure->cancelled))
                      <span class="text-danger">
                                                    {{ i18n.get('_.stationboard.stop-cancelled') }}
                                                </span>
                      @else
                      \Carbon\Carbon::parse($departure->plannedWhen)->isoFormat(i18n.get('_.time-format'))
                      @if(isset($departure->delay))
                      <small>(<span class="traindelay">
                                                            + $departure->delay / 60
                                                        </span>)</small>
                      @endif
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import StationForm from "../components/StationForm";

export default {
  name: "Stationboard",
  components: {StationForm}
}
</script>

<style scoped>

</style>