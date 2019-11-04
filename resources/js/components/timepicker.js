let input = document.getElementById("timepicker");

if (document.getElementById("timepicker-reveal")) {
    document
        .getElementById("timepicker-reveal")
        .addEventListener("click", e => {
            e.preventDefault();

            let reveal = document.getElementById("timepicker-form").classList;
            if (
                reveal.contains("opacity-null") ||
                reveal.contains("bounceOut")
            ) {
                reveal.remove("opacity-null");
                reveal.remove("bounceOut");
                reveal.add("animated");
                reveal.add("bounceIn");
            } else {
                reveal.remove("bounceIn");
                reveal.add("bounceOut");
                reveal.add("animated");
                setTimeout(() => {
                    reveal.add("opacity-null");
                }, 1000);
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

                //This is so completely ugly. Mabe we should reconsider this with moment.js?
                let splitDateTime = input.value.split("T");
                let splitDate = splitDateTime[0].split('-');
                let splitTime = splitDateTime[1].split(':');

                let utcDate = Date.UTC(splitDate[0], splitDate[1]-1, splitDate[2], splitTime[0], splitTime[1], 0);
                let offset = new Date(utcDate).getTimezoneOffset();
                let unixTimestamp = Math.floor((utcDate/1000)+(offset*60));

                window.location = window.changeTimeLink
                    .replace("&amp;", "&")
                    .replace("&amp;", "&")
                    .replace("&amp;", "&")
                    .replace("REPLACEME", unixTimestamp);
            };
        });
}
