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
        });
}
