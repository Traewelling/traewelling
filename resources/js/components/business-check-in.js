let statusBusiness;
let statusVisibility;
let statusBody;
let statusId          = 0;
let statusBodyElement = $("#status-body");

let businessCheckInput  = $("#business_check");
let businessButton      = $("#businessDropdownButton");
const businessIcons     = ["fa-user", "fa-briefcase", "fa-building"];
let visibilityFormInput = $("#checkinVisibility");
let visibilityButton    = $("#visibilityDropdownButton");
const visibilityIcons   = ["fa-globe-americas", "fa-lock-open", "fa-user-friends", "fa-lock", "fa-user-check"];

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

    statusId         = event.currentTarget.dataset.trwlStatusId;
    statusBody       = document.getElementById("status-" + statusId).dataset.trwlStatusBody;
    statusBusiness   = document.getElementById("status-" + statusId).dataset.trwlBusinessId;
    statusVisibility = document.getElementById("status-" + statusId).dataset.trwlVisibility;
    statusBodyElement.val(statusBody);
    businessCheckInput.val(statusBusiness);
    visibilityFormInput.val(statusVisibility);
    setIconForDropdown(statusBusiness, businessButton, businessCheckInput, businessIcons);
    setIconForDropdown(statusVisibility, visibilityButton, visibilityFormInput, visibilityIcons);
    $("#edit-modal").modal("show");
    document.querySelector('#body-length').innerText = document.querySelector('#status-body').value.length;
});

$(document).on("click", "#modal-trwl-edit-save", function () {
    $.ajax({
        method: "POST",
        url: urlEdit,
        data: {
            body: statusBodyElement.val(),
            statusId: statusId,
            business_check: businessCheckInput.val(),
            checkinVisibility: visibilityFormInput.val(),
            _token: token
        }
    }).done(function (msg) {
        window.location.reload();
    });
});
