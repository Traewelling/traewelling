let input = document.getElementById("timepicker");

console.log(input);

document.getElementById("timepicker-reveal").addEventListener("click", e => {
    e.preventDefault();

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

        let date = new Date(input.value);
        let unixTimestamp = Math.floor(date.getTime() / 1000);

        window.location = window.changeTimeLink
            .replace("&amp;", "&")
            .replace("&amp;", "&")
            .replace("&amp;", "&")
            .replace("REPLACEME", unixTimestamp);
    };
});
