let notificationsToggle = $('.notifications-board-toggle');
if (notificationsToggle !== undefined && notificationsToggle !== null) {


    let list  = document.getElementById("notifications-list");
    let empty = document.getElementById("notifications-empty");
    empty.classList.remove("d-none");

    while (list.childNodes.length > 3) {
        list.removeChild(list.firstChild);
    }
    fetch("/notifications/latest", {
        credentials: "same-origin",
        headers: new Headers({
            "Accept": "application/json"
            //This was responsible for the redirect-bug in #28.
            //Figuring that out didn't even take me a whole year! - signed 2021-04-13
        }),
    })
        .then(res => res.json())
        .then(notifications => {
            // If there are no notifications, we can just quit here. Else, show the items.
            if (notifications.length == 0) return;
            empty.classList.add("d-none");
            // if there are unread notifications, make the bell ring.
            if (notifications.some(n => n.read_at == null)) {
                Array.from(document.getElementsByClassName("notifications-bell"))
                    .forEach((bell) => {
                        bell.classList.replace("far", "fa");
                        bell.classList.add("bell");
                    });
            }

            const html = notifications.reduce((sum, add) => {
                return sum + add.html;
            }, "");

            list.insertAdjacentHTML("afterbegin", html);
        })
        .then(() => { // After the notification rows have been created, we can add eventlisteners for them
            Array.from(document.getElementsByClassName("toggleReadState")).forEach((btn) =>
                btn.addEventListener("click", () => {

                    fetch("/notifications/toggleReadState/" + btn.dataset.id, {
                        credentials: "same-origin",
                        method: "POST",
                        body: JSON.stringify({}),
                        headers: {
                            "X-CSRF-TOKEN": token
                        }
                    })
                        .then(res => {
                            toggleRead(btn.dataset.id, res.status == 201);
                        });
                })
            );
        });
}


document.getElementById("mark-all-read").addEventListener("click", () => {
    fetch("/notifications/readAll", {
        credentials: "same-origin",
        method: "POST",
        body: JSON.stringify({}),
        headers: {
            "X-CSRF-TOKEN": token
        }
    }).then(() => {
        Array.from(document.getElementsByClassName("toggleReadState")).forEach((btn) => {
            toggleRead(btn.dataset.id, true);
        });
    })
});

const toggleRead = (notificationId, isNewStateRead) => {
    const icon = document.querySelector("#notification-" + notificationId + " .toggleReadState i");
    const row  = document.getElementById("notification-" + notificationId);

    if (isNewStateRead) { // new state = read
        icon.classList.replace("fa-envelope", "fa-envelope-open");
        row.classList.remove("unread");
    } else { // new state = unread
        icon.classList.replace("fa-envelope-open", "fa-envelope");
        row.classList.add("unread");
    }
};
