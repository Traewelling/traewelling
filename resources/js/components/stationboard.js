import { longStackSupport } from "q";

let delays = document.getElementsByClassName("traindelay");
for (let i = 0; i < delays.length; i++) {
    let delay = delays[i].innerText;
    delay.slice(1);
    if (delay <= 3) {
        delays[i].classList.add("text-success");
    }
    if (delay > 3 && delay < 10) {
        delays[i].classList.add("text-warning");
    }
    if (delay >= 10) {
        delays[i].classList.add("text-danger");
    }
}

var touchmoved;
$(document)
    .on("click touchstart", ".trainrow", function () {
        var lineName = $(this).data("linename");
        var tripID = $(this).data("tripid");
        var start = $(this).data("start");
        if (touchmoved != true) {
            window.location =
                urlTrainTrip +
                "?tripID=" +
                tripID +
                "&lineName=" +
                lineName +
                "&start=" +
                start;
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
        var tripID = $(this)
            .parent()
            .parent()
            .data("tripid");
        var start = $(this)
            .parent()
            .parent()
            .data("start");
        var destination = $(this).data("ibnr");
        var stopname = $(this).data("stopname");
        var linename = $(this)
            .parent()
            .parent()
            .data("linename");
        if (touchmoved != true) {
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
                modal.find("#input-start").val(start);
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

if (document.getElementById("gps-button")) {
    document.getElementById("gps-button").addEventListener("click", () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(searchStationByPosition, handlePositioningError);
        } else {
            ["d-none", "animated", "fadeIn"].forEach(classname =>
                document.getElementById("gps-disabled-error").classList.toggle(classname)
            );
        }
    });
}

function searchStationByPosition(position) {
    let newLocation = `${window.location.protocol}//${window.location.host}/trains/nearby?latitude=${position.coords.latitude}&longitude=${position.coords.longitude}`;
    window.location.href = newLocation;
}

function handlePositioningError(error) {
    ["d-none", "animated", "fadeIn"].forEach(classname =>
        document.getElementById("gps-disabled-error").classList.toggle(classname)
    );
}