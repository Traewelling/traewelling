let statusBusiness;
let statusBody;
let statusId = 0;

let businessCheckInput = $("#business_check");
let dropDownButton     = $("#businessDropdownButton");
let dropDown           = $("#businessDropdown");
const businessIcons    = ["fa-user", "fa-briefcase", "fa-building"];

function setIconsForCheckIn(value) {
    let number  = parseInt(value, 10);
    let classes = dropDownButton.children()[0].classList;
    businessIcons.forEach((value) => {
        classes.remove(value);
    });
    classes.add(businessIcons[number]);
    businessCheckInput.val(number);
}

$(".trwl-business-item").on("click", function (event) {
    setIconsForCheckIn(event.currentTarget.dataset.trwlBusiness);
});

$(document).on("click", ".edit", function (event) {
    event.preventDefault();

    statusId       = event.currentTarget.dataset.trwlStatusId;
    statusBody     = document.getElementById("status-" + statusId).dataset.trwlStatusBody;
    statusBusiness = document.getElementById("status-" + statusId).dataset.trwlBusinessId;
    $("#status-body").val(statusBody);
    $("#business_check").val(statusBusiness);
    setIconsForCheckIn(statusBusiness);
    $("#edit-modal").modal("show");
});

$(document).on("click", "#modal-trwl-edit-save", function () {
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