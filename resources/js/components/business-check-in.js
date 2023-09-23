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

    let statusId = event.currentTarget.dataset.trwlStatusId;
    let dataset  = document.getElementById("status-" + statusId).dataset;

    document.querySelector("#status-update input[name='statusId']").value = statusId;
    document.querySelector("#status-update textarea[name='body']").value  = dataset.trwlStatusBody;
    document.querySelector("#status-update input[name='manualDeparture']").value  = dataset.trwlManualDeparture;
    document.querySelector("#status-update input[name='manualArrival']").value  = dataset.trwlManualArrival;

    let statusBusiness   = dataset.trwlBusinessId;
    let statusVisibility = dataset.trwlVisibility;
    businessCheckInput.val(statusBusiness);
    visibilityFormInput.val(statusVisibility);
    setIconForDropdown(statusBusiness, businessButton, businessCheckInput, businessIcons);
    setIconForDropdown(statusVisibility, visibilityButton, visibilityFormInput, visibilityIcons);

    //Clear list
    document.querySelector("#status-update select[name='destinationStopoverId']").innerHTML = "";

    let alternativeDestinations = JSON.parse(dataset.trwlAlternativeDestinations);
    if (alternativeDestinations) {
        document.querySelector('.destination-wrapper').classList.remove('d-none');
        for (let destId in alternativeDestinations) {
            let dest            = alternativeDestinations[destId];
            let stopoverId      = dest.id;
            let stopoverName    = dest.name;
            let stopoverArrival = dest.arrival_planned;

            let stopoverOption   = document.createElement("option");
            stopoverOption.value = stopoverId;
            stopoverOption.text  = stopoverArrival + ': ' + stopoverName;
            document.querySelector("#status-update select[name='destinationStopoverId']").appendChild(stopoverOption);
        }
        document.querySelector("#status-update select[name='destinationStopoverId']").value = dataset.trwlDestinationStopover;
    } else {
        document.querySelector('.destination-wrapper').classList.add('d-none');
    }

    const modal = new window.modal($("#edit-modal"));
    modal.show();
    document.querySelector('#body-length').innerText = document.querySelector('#status-body').value.length;
});
