let statusBusiness;
let statusBody;
var statusId = 0;

let businessUserIcon = $("#business-user");
let businessBriefcaseIcon = $("#business-briefcase");
let businessBuildingIcon = $("#business-building");

let businessCheckInput = $("#business_check");
let businessCheckInputNewTravel = $("#business_check_new_travel");
$(businessCheckInput).val("0");

let businessUser = $("#business-li-user");
let businessBriefcase = $("#business-li-briefcase");
let businessBuilding = $("#business-li-building");

function setIconsForCheckIn(number){
    switch (number){
        case 1:
            $(businessUserIcon).addClass("d-none");
            $(businessBriefcaseIcon).removeClass("d-none");
            $(businessBuildingIcon).addClass("d-none");
            $(businessCheckInput).val("1");
            $(businessCheckInputNewTravel).val("1");
            break;
        case 2:
            $(businessUserIcon).addClass("d-none");
            $(businessBriefcaseIcon).addClass("d-none");
            $(businessBuildingIcon).removeClass("d-none");
            $(businessCheckInput).val("2");
            $(businessCheckInputNewTravel).val("2");
            break;
        default:
            $(businessUserIcon).removeClass("d-none");
            $(businessBriefcaseIcon).addClass("d-none");
            $(businessBuildingIcon).addClass("d-none");
            $(businessCheckInput).val("0");
            $(businessCheckInputNewTravel).val("0");
            break;
    }
}

$(businessUser).on("click", function (){
    setIconsForCheckIn(0)
})

$(businessBriefcase).on("click", function (){
    setIconsForCheckIn(1)
})

$(businessBuilding).on("click", function (){
    setIconsForCheckIn(2)
})

$(document).on("click", ".edit", function (event) {
    console.log("edit");
    event.preventDefault();

    statusId = event.target.parentElement.dataset["statusid"];
    statusBody = document.getElementById("status-" + statusId).dataset["body"];
    statusBusiness = document.getElementById("status-" + statusId).dataset["businessid"];

    $("#status-body").val(statusBody);
    $("#business_check").val(statusBusiness);
    setIconsForCheckIn(parseInt(statusBusiness));
    $("#edit-modal").modal("show");
});

$(document).on("click", "#modal-save", function () {
    $.ajax({
        method: "POST",
        url: urlEdit,
        data: {
            body: $("#status-body").val(),
            statusId: statusId,
            business_check: $("#business_check").val(),
            _token: token
        }
    }).done(function (msg) {
        window.location.reload();
    });
});