window.addEventListener("load", () => {
    let invalidBox = document.createElement("div");
    let input = document.getElementById("timepicker");
    input.value = document.getElementById("reqTime").innerText;

    document
        .getElementById("timepicker-button")
        .addEventListener("click", () => {
            let cl = document.getElementById("timepicker-form").classList;
            cl.remove("opacity-null");
            cl.add("animated");
            cl.add("bounceIn");

            document
                .getElementById("timepicker-button")
                .addEventListener("click", e => {
                    e.preventDefault();
                    changeTime();
                });
            input.addEventListener("keyup", function(event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    changeTime();
                }
            });

            const changeTime = () => {
                invalidBox.classList.add("d-none");
                input.classList.remove("is-invalid");

                let r = /^(\d{2})\:*(\d{2})$/;
                let matches = Array.from(input.value.matchAll(r))[0];

                if (
                    typeof matches != "undefined" &&
                    matches[1] >= 0 &&
                    matches[1] < 24 &&
                    matches[2] >= 0 &&
                    matches[2] < 60
                ) {
                    let d = new Date();
                    d.setHours(matches[1], matches[2]);
                    let ts = Math.floor(d.getTime() / 1000);

                    window.location = window.changeTimeLink
                        .replace("&amp;", "&")
                        .replace("&amp;", "&")
                        .replace("REPLACEME", ts);
                } else {
                    invalidBox.classList.add("invalid-feedback");
                    invalidBox.classList.add("animated");
                    invalidBox.classList.add("fadeIn");
                    invalidBox.classList.remove("d-none");
                    invalidBox.innerText = window.invalidHHMMdate;
                    document
                        .getElementById("timepicker-form")
                        .children[0].appendChild(invalidBox);
                    input.classList.add("is-invalid");
                }
            };
        });
});
