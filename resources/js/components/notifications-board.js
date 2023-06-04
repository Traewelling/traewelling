let pills = Array.from(document.getElementsByClassName("notifications-pill"));

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
