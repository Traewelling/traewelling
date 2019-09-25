Array.from(document.getElementsByClassName("progress-time")).forEach(
    element => {
        const begin = parseInt(
            element.attributes.getNamedItem("aria-valuemin").nodeValue
        );
        const end = parseInt(
            element.attributes.getNamedItem("aria-valuemax").nodeValue
        );

        const interval = setInterval(() => {
            const now = Math.floor(new Date().getTime() / 1000);
            element.attributes["aria-valuenow"] = now;

            const percentage = Math.round(
                (100 * (now - begin)) / (end - begin)
            );
            element.style.width = percentage + "%";

            // We don't need to revisit all the progress-bars all the time, if the trip already ended.
            if (now > end) {
                clearInterval(interval);
            }
        }, 5 * 1000);
    }
);
