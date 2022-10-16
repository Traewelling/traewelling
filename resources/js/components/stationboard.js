let delays = document.getElementsByClassName("traindelay");
for (let delay of delays) {
    let delayValue = delay.innerText;
    delayValue.slice(1);
    if (delayValue <= 3) {
        delay.classList.add("text-success");
    }
    if (delayValue > 3 && delayValue < 10) {
        delay.classList.add("text-warning");
    }
    if (delayValue >= 10) {
        delay.classList.add("text-danger");
    }
}

var touchmoved;
$(document)
    .on("click touchstart", ".trainrow", function () {
        var lineName        = $(this).data("linename");
        var tripID          = $(this).data("tripid");
        var start           = $(this).data("start");
        let departure       = $(this).data("departure");
        let searchedStation = $(this).data('searched-station')
        if (!touchmoved) {
            let redirectUrl = urlTrainTrip +
                "?tripID=" +
                encodeURIComponent(tripID) +
                "&lineName=" +
                encodeURIComponent(lineName) +
                "&start=" +
                encodeURIComponent(start) +
                "&departure=" +
                encodeURIComponent(departure);

            if (searchedStation !== undefined) {
                redirectUrl += '&searchedStation=' + encodeURIComponent(searchedStation);
            }

            window.location = redirectUrl;
        }
    })
    .on("touchmove", function (e) {
        touchmoved = true;
    })
    .on("touchstart", function () {
        touchmoved = false;
    });

$(document)
    .on("click touchend", ".train-destinationrow", function () {
        var tripID      = $(this)
            .parent()
            .parent()
            .data("tripid");
        var destination = $(this).data("ibnr");
        var stopname    = $(this).data("stopname");
        var arrival     = $(this).data("arrival");
        var linename    = $(this)
            .parent()
            .parent()
            .data("linename");
        if (!touchmoved) {
            $("#checkinModal").modal("show", function (event) {
                var modal = $(this);
                modal
                    .find(".modal-title")
                    .html(
                        linename +
                        ' <i class="fas fa-arrow-alt-circle-right"></i> ' +
                        stopname
                    );
                modal.find("#input-tripID").val(tripID);
                modal.find("#input-destination").val(destination);
                modal.find("#input-arrival").val(arrival);
            });
        }
    })
    .on("touchmove", function (e) {
        touchmoved = true;
    })
    .on("touchstart", function () {
        touchmoved = false;
    });

$("#checkinModal").on("show.bs.modal", function (event) {
    $(event.relatedTarget);
});

$("#checkinButton").click(function (e) {
    e.preventDefault();
    $("#checkinForm").submit();
});

if (document.getElementById("history-button")) {
    document.getElementById("history-button").addEventListener("click", () => {
        ["d-none", "animated", "fadeIn"].forEach(classname =>
            document.getElementById("last-stations").classList.toggle(classname)
        );
    });
}
