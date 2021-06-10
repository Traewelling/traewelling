<template>
  <transition>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
          <div class="card">
            <div class="card-header" data-linename=" $hafasTrip->linename "
                 data-startname=" $hafasTrip->originStation->name " data-start=" request()->start "
                 data-tripid=" $hafasTrip->trip_id ">
              <div class="float-end">
                <a href="#" class="train-destinationrow"
                   data-ibnr="$terminalStop['stop']['id']"
                   data-stopname="$terminalStop['stop']['name']"
                   data-arrival="$terminalStop['plannedArrival']">
                  <i class="fa fa-fast-forward"></i>
                </a>
              </div>
              @if (file_exists(public_path('img/'.$hafasTrip->category.'.svg')))
              <img class="product-icon" src=" asset('img/'.$hafasTrip->category.'.svg') "/>
              @else
              <i class="fa fa-train"></i>
              @endif
              $hafasTrip->linename
              <i class="fas fa-arrow-alt-circle-right"></i>
              $hafasTrip->destinationStation->name
            </div>

            <div class="card-body p-0 table-responsive">
              <table class="table table-dark table-borderless table-hover m-0"
                     data-linename=" $hafasTrip->linename "
                     data-startname=" $hafasTrip->originStation->name "
                     data-start=" request()->start "
                     data-tripid=" $hafasTrip->trip_id ">
                <thead>
                  <tr>
                    <th>{{ i18n.get('_.stationboard.stopover') }}</th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($stopovers as $stop)
                  @if(!\Carbon\Carbon::parse($stop['plannedArrival'])->isAfter(\Carbon\Carbon::parse(request()->departure)))
                  @continue
                  @endif

                  @if(@$stop['cancelled'] == 'true')
                  <tr>
                    <td> $stop['stop']['name']</td>
                    <td>
                      <span class="text-danger">{{ i18n.get('_.stationboard.stop-cancelled') }}</span><br/>&nbsp;
                    </td>
                    <td> $stop['departurePlatform']</td>
                  </tr>
                  @else
                  <tr class="train-destinationrow"
                      data-ibnr="$stop['stop']['id']"
                      data-stopname="$stop['stop']['name']"
                      data-arrival="$stop['plannedArrival']">
                    <td> $stop['stop']['name']</td>
                    <td>
                      @if($stop['plannedArrival'] != null)
                      {{ i18n.get('_.stationboard.arr') }}
                      \Carbon\Carbon::parse($stop['plannedArrival'])->isoFormat(i18n.get('_.time-format'))
                      @if(isset($stop['arrivalDelay']))
                      <small>(<span
                          class="traindelay">+ $stop['arrivalDelay'] / 60 </span>)</small>
                      @endif
                      @endif
                      <br/>
                      @if($stop['plannedDeparture'] != null)
                      {{ i18n.get('_.stationboard.dep') }}
                      \Carbon\Carbon::parse($stop['plannedDeparture'])->isoFormat(i18n.get('_.time-format'))
                      @if(isset($stop['departureDelay']))
                      <small>(<span
                          class="traindelay">+ $stop['departureDelay']/60 </span>)</small>
                      @endif
                      @endif
                    </td>
                    <td> $stop['departurePlatform']</td>
                  </tr>
                  @endif
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </transition>
</template>

<script>
export default {
  name: "Trip",
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.loading     = true;
      this.station     = null;
      const when       = this.$route.query.when ?? "";
      const travelType = this.$route.query.travelType ?? "";
      const query      = this.$route.query;
      axios
          .get('/trains/trip?tripID=' + query.tripID + "&lineName=" + query.lineName + "&start=" + query.start)
          .then((result) => {
            // this.station    = result.data.meta.station;
            // this.times      = result.data.meta.times;
            // this.departures = result.data.data;
            console.log(result);
            this.loading    = false;

          })
          .catch((error) => {
            console.error(error);
          });
    },
  }
};
</script>

<style scoped>

</style>
