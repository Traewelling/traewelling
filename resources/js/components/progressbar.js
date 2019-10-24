Array.from(document.getElementsByClassName("progress-time")).forEach(
    element => {
        const begin = parseInt(
            element.attributes.getNamedItem("aria-valuemin").nodeValue
        );
        const end = parseInt(
            element.attributes.getNamedItem("aria-valuemax").nodeValue
        );

        const update = () => {
            const now = Math.floor(new Date().getTime() / 1000);
            element.attributes["aria-valuenow"] = now;

            let percentage = 0;
            if (begin == end) {
                // Edge Case for DIV/0
                if (now < begin) {
                    // status is in the future
                    percentage = 0;
                } else {
                    // status was in the past
                    // now > end, weil begin==end
                    percentage = 100;
                }
            } else {
                percentage = Math.round((100 * (now - begin)) / (end - begin));
            }
            element.style.width = percentage + "%";

            // We don't need to revisit all the progress-bars all the time, if the trip already ended.
            if (now > end) {
                clearInterval(interval);
            }
        };

        const interval = setInterval(update, 5 * 1000);
        update();
    }
);
