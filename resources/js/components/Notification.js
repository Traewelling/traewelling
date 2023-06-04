window.Notification = class Notification {

    static unread() {
        return API.request('/notifications/unread/count');
    }

    static latest() {
        return API.request('/notifications');
    }
}

let elementNotificationsToggle = document.getElementById('notifications-toggle');
if (elementNotificationsToggle) {
    //If element exists: user is probably signed in -> load and display count of unread notifications
    Notification.unread().then(function (request) {
        request.json().then(function (json) {
            let unreadCount = json.data;
            if (unreadCount === 0) {
                return;
            }
            let notificationCounters = document.querySelectorAll('.notifications-pill');
            notificationCounters.forEach((counter) => {
                //There are two different dom elements for mobile and desktop -> forEach
                counter.removeAttribute("hidden");
                counter.innerText = unreadCount;
            });

            let notificationIcons = document.querySelectorAll('.notifications-bell');
            notificationIcons.forEach((icon) => {
                //There are two different dom elements for mobile and desktop -> forEach
                icon.classList.replace("far", "fa");
            });
        });
    });
}


document.querySelectorAll('button.notifications-board-toggle').forEach(button => {
    button.addEventListener('click', () => {
        console.log('...');
    });
});
