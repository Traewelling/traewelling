import { Modal } from "bootstrap";

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
    .on("click touchend", ".trainrow", function () {
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
            const modalWrapper = $("#checkinModal");
            const modal = new Modal(modalWrapper);
            modalWrapper
                .find(".modal-title")
                .html(
                    linename +
                    ' <i class="fas fa-arrow-alt-circle-right"></i> ' +
                    stopname
                );
            modalWrapper.find("#input-tripID").val(tripID);
            modalWrapper.find("#input-destination").val(destination);
            modalWrapper.find("#input-arrival").val(arrival);

            modal.show();
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
