Array.from(document.getElementsByClassName("progress-time")).forEach(
    element => {
        const departure = element.dataset.valuemin;
        const arrival = element.dataset.valuemax;

        const update = () => {
            const now = Math.floor(new Date().getTime() / 1000);
            element.dataset.valuenow = now;

            let percentage = 0;
            if (departure == arrival) {
                // Edge Case for DIV/0
                if (now < arrival) {
                    // status is in the future
                    percentage = 0;
                } else {
                    // status was in the past
                    // now > end, weil begin==end
                    percentage = 100;
                }
            } else {
                percentage = 100 * ((now - departure) / (arrival - departure));
            }
            element.style.width = percentage + "%";

            // We don't need to revisit all the progress-bars all the time, if the trip already ended.
            if (now > arrival) {
                clearInterval(interval);
            }
        };

        const interval = setInterval(update, 5 * 1000);
        update();
    }
);
