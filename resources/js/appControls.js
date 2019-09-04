var statusId = 0;
var statusBodyElement = null;

$(document).on("click", ".edit", function(event) {
    console.log("edit");
    event.preventDefault();

    statusBodyElement = event.target.parentNode.parentNode.childNodes[0];
    console.log(statusBodyElement);
    var statusBody = statusBodyElement.textContent;

    statusId = event.target.parentNode.parentNode.dataset["statusid"];
    $("#status-body").val(statusBody);
    $("#edit-modal").modal();
});

$(document).on("click", ".delete", function(event) {
    console.log("delete");
    event.preventDefault();
    statusId = event.target.parentNode.parentNode.dataset["statusid"];
    console.log(statusId);
    $.ajax({
        method: "DELETE",
        url: urlDelete,
        data: {statusId: statusId, _token: token}
    }).done(function(msg) {
        event.target.parentNode.parentNode.parentNode.remove();
    });
});

$(document).on("click", "#modal-save", function() {
    $.ajax({
        method: "POST",
        url: urlEdit,
        data: {body: $("#status-body").val(), statusId: statusId, _token: token}
    }).done(function(msg) {
        $(statusBodyElement).text(msg["new_body"]);
        $("#edit-modal").modal("hide");
    });
});

$(document).on("click", ".like", function(event) {
    event.preventDefault();

    statusId = event.target.parentNode.parentNode.dataset["statusid"];
    console.log(statusId);
    if (event.target.innerText == "Like") {
        $.ajax({
            method: "POST",
            url: urlLike,
            data: {statusId: statusId, _token: token}
        }).done(function() {
            event.target.innerText = "Dislike";
        });
    } else {
        $.ajax({
            method: "POST",
            url: urlDislike,
            data: {statusId: statusId, _token: token}
        }).done(function() {
            event.target.innerText = "Like";
        });
    }
});

$(document).on("click", ".disconnect", function(event) {
    event.preventDefault();

    var provider = event.target.dataset["provider"];
    $.ajax({
        method: "POST",
        url: urlDisconnect,
        data: {provider: provider, _token: token}
    }).done(function() {
        location.reload();
    });
});
