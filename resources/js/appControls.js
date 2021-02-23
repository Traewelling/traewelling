var statusId = 0;
var statusBodyElement = null;

$(document).on("click", ".edit", function (event) {
    console.log("edit");
    event.preventDefault();

    statusId = event.target.parentElement.dataset["statusid"];
    statusBody = document.getElementById("status-" + statusId).dataset["body"];
    $("#status-body").val(statusBody);
    $("#edit-modal").modal();
});

$(document).on("click", "#modal-save", function () {
    $.ajax({
        method: "POST",
        url: urlEdit,
        data: {
            body: $("#status-body").val(),
            statusId: statusId,
            businessCheck: $("#business_check:checked").length,
            _token: token
        }
    }).done(function (msg) {
        window.location.reload();
    });
});

$(document).on("click", ".delete", function (event) {
    event.preventDefault();

    statusId = event.target.parentElement.dataset["statusid"];
    $("#delete-modal").modal();
});

$(document).on("click", "#modal-delete", function () {
    $.ajax({
        method: "DELETE",
        url: urlDelete,
        data: { statusId: statusId, _token: token }
    }).done(function (msg) {
        window.location.replace("/dashboard");
    });
});

$(document).on("click", ".like", function (event) {
    statusId = event.target.dataset["statusid"];

    var $likecount = document.getElementById("like-count-" + statusId);
    var $smallavatar = document.getElementById("avatar-small-" + statusId);
    var count = parseInt($likecount.innerText);

    if (event.target.className == "like far fa-star") {
        $.ajax({
            method: "POST",
            url: urlLike,
            data: { statusId: statusId, _token: token }
        }).done(function () {
            event.target.className = "like fas fa-star animated bounceIn";
            $likecount.innerText = ++count;

            if (count == 0) {
                $likecount.classList.add("d-none");
                if ($smallavatar.dataset["selflike"] == "1") {
                    $smallavatar.classList.remove("d-none");
                }
            } else {
                $likecount.classList.remove("d-none");
                if ($smallavatar.dataset["selflike"] == "1") {
                    $smallavatar.classList.add("d-none");
                }
            }
        });
    } else {
        $.ajax({
            method: "POST",
            url: urlDislike,
            data: { statusId: statusId, _token: token }
        }).done(function () {
            event.target.className = "like far fa-star";
            $likecount.innerText = --count;

            if (count == 0) {
                $likecount.classList.add("d-none");
                if ($smallavatar.dataset["selflike"] == "1") {
                    $smallavatar.classList.remove("d-none");
                }
            } else {
                $likecount.classList.remove("d-none");
                if ($smallavatar.dataset["selflike"] == "1") {
                    $smallavatar.classList.add("d-none");
                }
            }
        });
    }

    event.preventDefault();
    event.stopPropagation();
});

$(document).on("click", ".follow", function (event) {
    event.preventDefault();

    userId = event.target.dataset["userid"];
    console.log(userId);
    if (event.target.dataset["following"] == "no") {
        $.ajax({
            method: "POST",
            url: urlFollow,
            data: { follow_id: userId, _token: token }
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
            data: { follow_id: userId, _token: token }
        }).done(function () {
            event.target.dataset["following"] = "no";
            event.target.classList.add("btn-primary");
            event.target.classList.remove("btn-danger");
            event.target.innerText = window.translFollow;
        });
    }
});

$(document).on("click", ".disconnect", function (event) {
    event.preventDefault();

    var provider = event.target.dataset["provider"];
    $.ajax({
        method: "POST",
        url: urlDisconnect,
        data: { provider: provider, _token: token },
        success: function () {
            location.reload();
        },
        error: function (request, status, error) {
            bootstrap_alert.danger(request.responseText);
        }
    });
});
