var statusId = 0;
var statusBodyElement = null;

$(document).on("click", ".edit", function(event) {
    console.log("edit");
    event.preventDefault();

    statusBody = event.target.parentNode.parentNode.parentNode.dataset['body'];
    statusId = event.target.parentNode.parentNode.parentNode.dataset["statusid"];
    $("#status-body").val(statusBody);
    $("#edit-modal").modal();
});

$(document).on("click", ".delete", function(event) {
    console.log("delete");
    event.preventDefault();
    statusId = event.target.parentNode.parentNode.parentNode.dataset["statusid"];
    console.log(statusId);
    $.ajax({
        method: "DELETE",
        url: urlDelete,
        data: {statusId: statusId, _token: token}
    }).done(function(msg) {
        window.location.replace('/dashboard');
    });
});

$(document).on("click", "#modal-save", function() {
    $.ajax({
        method: "POST",
        url: urlEdit,
        data: {body: $("#status-body").val(), statusId: statusId, _token: token}
    }).done(function(msg) {
        window.location.reload();
    });
});

$(document).on("click", ".like", function(event) {
    event.preventDefault();

    statusId = event.target.parentNode.parentNode.dataset["statusid"];
    console.log(statusId);
    if (event.target.className == "like far fa-heart") {
        $.ajax({
            method: "POST",
            url: urlLike,
            data: {statusId: statusId, _token: token}
        }).done(function() {
            event.target.className = "like fas fa-heart";
        });
    } else {
        $.ajax({
            method: "POST",
            url: urlDislike,
            data: {statusId: statusId, _token: token}
        }).done(function() {
            event.target.className = "like far fa-heart";
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
