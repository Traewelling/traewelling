let statusBusiness;
let statusBody;
let statusId = 0;

let businessCheckInput = $("#business_check");
let businessButton     = $("#businessDropdownButton");
const businessIcons    = ["fa-user", "fa-briefcase", "fa-building"];
let visibilityFormInput= $("#visibility_input");
let visibilityButton   = $("#visibilityDropdownButton");
const visibilityIcons  = ["fa-globe-americas", "fa-lock-open", "fa-user-friends", "fa-lock"];

function setIconForDropdown(value, button, inputFieldValue, icons) {
    let number  = parseInt(value, 10);
    let classes = button.children()[0].classList;
    icons.forEach((value) => {
        classes.remove(value);
    });
    classes.add(icons[number]);
    inputFieldValue.val(number);
}

$(".trwl-business-item").on("click", function (event) {
    setIconForDropdown(event.currentTarget.dataset.trwlBusiness, businessButton, businessCheckInput, businessIcons);
});

$(".trwl-visibility-item").on("click", function (event) {
    setIconForDropdown(event.currentTarget.dataset.trwlVisibility, visibilityButton, visibilityFormInput, visibilityIcons);
});

$(document).on("click", ".edit", function (event) {
    event.preventDefault();

    statusId       = event.currentTarget.dataset.trwlStatusId;
    statusBody     = document.getElementById("status-" + statusId).dataset.trwlStatusBody;
    statusBusiness = document.getElementById("status-" + statusId).dataset.trwlBusinessId;
    $("#status-body").val(statusBody);
    $("#business_check").val(statusBusiness);
    setIconForDropdown(statusBusiness, businessButton, businessCheckInput, businessIcons);
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