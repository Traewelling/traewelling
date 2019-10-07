window.addEventListener("load", () => {
    let input = document.getElementById("timepicker");

    document
        .getElementById("timepicker-reveal")
        .addEventListener("click", () => {
            let reveal = document.getElementById("timepicker-form").classList;
            if (reveal.contains("opacity-null") || reveal.contains("bounceOut")) {
                reveal.remove("opacity-null");
                reveal.remove("bounceOut");
                reveal.add("animated");
                reveal.add("bounceIn");
            } else {
                reveal.remove("bounceIn");
                reveal.add("bounceOut");
                reveal.add("animated");
            }

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
                input.classList.remove("is-invalid");

                let date = new Date(input.value);
                let unixTimestamp = Math.floor(date.getTime() / 1000);

                 window.location = window.changeTimeLink
                     .replace("&amp;", "&")
                     .replace("&amp;", "&")
                     .replace("REPLACEME", unixTimestamp);
            };
        });
});
