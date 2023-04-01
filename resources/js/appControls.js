$(document).on("click", ".delete", function (event) {
    event.preventDefault();

    deleteStatusId = getDataset(event).trwlStatusId;
    $("#delete-modal").modal("show");
});

$(document).on("click", ".join", function (event) {
    event.preventDefault();

    const source = getDataset(event);
    $("#checkinModal").modal("show", function (event) {
        const modal = $(this);
        modal
            .find(".modal-title")
            .html(
                source.trwlLinename +
                ' <i class="fas fa-arrow-alt-circle-right"></i> ' +
                source.trwlStopName
            );
        modal.find("#input-tripID").val(source.trwlTripId);
        modal.find("#input-destination").val(source.trwlDestination);
        modal.find("#input-arrival").val(source.trwlArrival);
        modal.find("#input-start").val(source.trwlStart);
        modal.find("#input-departure").val(source.trwlDeparture);
    });
});

$(document).on("click", "#modal-delete", function () {
    $.ajax({
        method: "DELETE",
        url: urlDelete,
        data: {statusId: deleteStatusId, _token: token}
    }).done(function () {
        window.location.replace("/dashboard");
    });
});

$(document).on("click", ".like", function (event) {
    hasLikeFromUser = getDataset(event).trwlLiked;
    statusId        = getDataset(event).trwlStatusId;

    let $likeCount = document.getElementById("like-count-" + statusId);
    let $likeIcon  = document.getElementById("like-icon-" + statusId);
    let count      = parseInt($likeCount.innerText);

    if (!hasLikeFromUser) {
        $.ajax({
            method: "POST",
            url: urlLike,
            data: {statusId: statusId, _token: token}
        }).done(function () {
            getDataset(event).trwlLiked = true;

            $likeIcon.className  = "fas fa-star animated bounceIn";
            $likeCount.innerText = ++count;

            if (count === 0) {
                $likeCount.classList.add("d-none");
            } else {
                $likeCount.classList.remove("d-none");
            }
        });
    } else {
        $.ajax({
            method: "POST",
            url: urlDislike,
            data: {statusId: statusId, _token: token}
        }).done(function () {
            getDataset(event).trwlLiked = false;

            $likeIcon.className  = "far fa-star";
            $likeCount.innerText = --count;

            if (count === 0) {
                $likeCount.classList.add("d-none");
            } else {
                $likeCount.classList.remove("d-none");
            }
        });
    }

    event.stopPropagation();
});

$(document).on("click", ".follow", function (event) {
    event.preventDefault();
    let userId         = event.target.dataset["userid"];
    let privateProfile = event.target.dataset["private"];
    let following      = event.target.dataset["following"];

    if (privateProfile === "no") {
        if (following === "no") {
            $.ajax({
                method: "POST",
                url: urlFollow,
                data: {follow_id: userId, _token: token}
            }).done(function () {
                event.target.dataset["following"] = "yes";
                event.target.classList.add("btn-danger");
                event.target.classList.remove("btn-primary");
                event.target.innerText = window.translUnfollow;
            });
        } else {
            $.ajax({
                method: "POST",
                url: urlUnfollow,
                data: {follow_id: userId, _token: token}
            }).done(function () {
                event.target.dataset["following"] = "no";
                event.target.classList.add("btn-primary");
                event.target.classList.remove("btn-danger");
                event.target.innerText = window.translFollow;
            });
        }
    } else {
        if (following === "no") {
            $.ajax({
                method: "POST",
                url: urlFollowRequest,
                data: {follow_id: userId, _token: token}
            }).done(function () {
                event.target.dataset["following"] = "yes";
                event.target.classList.add("disabled");
                event.target.innerText = window.translPending;
            });
        } else {
            $.ajax({
                method: "POST",
                url: urlUnfollow,
                data: {follow_id: userId, _token: token}
            }).done(function () {
                location.reload();
            });
        }
    }
});

$(document).on("click", ".disconnect", function (event) {
    event.preventDefault();

    let provider = event.target.dataset["provider"];
    $.ajax({
        method: "POST",
        url: urlDisconnect,
        data: {provider: provider, _token: token},
        success: function () {
            location.reload();
        },
        error: function (request) {
            bootstrap_alert.danger(request.responseText);
        }
    });
});

$(document).on("click", ".trwl-share", function (event) {
    event.preventDefault();

    let shareText = getDataset(event).trwlShareText;
    let shareUrl  = getDataset(event).trwlShareUrl;

    if (navigator.share) {
        navigator.share({
            title: "Träwelling",
            text: shareText,
            url: shareUrl
        })
            .catch(console.error);
    } else {
        navigator.clipboard.writeText(shareText + " " + shareUrl)
            .then(() => {
                window.notyf.success('Copied to clipboard');
            });
    }

});

function getDataset(event) {
    let target = event.target.dataset;
    let parent = event.target.parentElement.dataset;

    return _.size(event.target.dataset) ? target : parent;
}