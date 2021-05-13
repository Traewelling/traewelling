let notificationsToggle = $('.notifications-board-toggle');
let pills = Array.from(document.getElementsByClassName("notifications-pill"));
if (notificationsToggle !== undefined && notificationsToggle !== null) {


    let list = document.getElementById("notifications-list");
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
                    });
                pills.forEach((pill) => {
                    pill.removeAttribute("hidden");
                    pill.innerText = itemCounter(notifications);
                });
            }

            const html = notifications.reduce((sum, add) => {
                return sum + add.html;
            }, "");

            list.insertAdjacentHTML("afterbegin", html);
        })
        .then(() => { // After the notification rows have been created, we can add eventListeners for them
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

const itemCounter = (array) => array.flat(Infinity).filter(current => current.read_at == null).length;

const toggleRead = (notificationId, isNewStateRead) => {
    const icon = document.querySelector("#notification-" + notificationId + " .toggleReadState i");
    const row = document.getElementById("notification-" + notificationId);
    let notificationsCount = parseInt(pills[0].innerText);

    if (isNewStateRead) { // new state = read
        icon.classList.replace("fa-envelope", "fa-envelope-open");
        row.classList.remove("unread");
        notificationsCount--;
        if (notificationsCount <= 0) {
            notificationsCount = 0;
            pills.forEach((pill) => {
                pill.setAttribute("hidden", true);
            });
        }
        pills.forEach((pill) => {
            pill.innerText = notificationsCount;
        });
    } else { // new state = unread
        icon.classList.replace("fa-envelope-open", "fa-envelope");
        row.classList.add("unread");
        if (notificationsCount == 0) {
            pills.forEach((pill) => {
                pill.removeAttribute("hidden");
            });
        }
        notificationsCount++;
        pills.forEach((pill) => {
            pill.innerText = notificationsCount;
        });
    }
};
