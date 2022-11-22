if (document.getElementById("gps-button")) {
    document.getElementById("gps-button").addEventListener("click", () => {
        document.querySelector('#gps-button .fa').classList.add('d-none');
        document.querySelector('#gps-button .spinner-border').classList.remove('d-none');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(searchStationByPosition, handlePositioningError);
        } else {
            handlePositioningError();
        }
    });

    function searchStationByPosition(position) {
        window.location.href = `${window.location.protocol}//${window.location.host}/trains/nearby?latitude=${position.coords.latitude}&longitude=${position.coords.longitude}`;
    }

    function handlePositioningError(error) {
        document.querySelector('#gps-button .fa').classList.remove('d-none');
        document.querySelector('#gps-button .spinner-border').classList.add('d-none');
        notyf.error(translations.stationboard.position_unavailable);
    }
}

