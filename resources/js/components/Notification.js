window.Notification = class Notification {

    static refreshUnreadCount() {
        return API.request('/notifications/unread/count')
            .then(function (request) {
                request.json().then(function (json) {
                    let unreadCount = json.data;

                    //There are two different dom elements for mobile and desktop -> querySelectorAll -> forEach
                    let notificationCounters = document.querySelectorAll('.notifications-pill');
                    let notificationIcons    = document.querySelectorAll('.notifications-bell');

                    if (unreadCount === 0) {
                        notificationCounters.forEach((counter) => {
                            counter.setAttribute("hidden", "hidden");
                            counter.innerText = unreadCount;
                        });
                        notificationIcons.forEach((icon) => {
                            icon.classList.replace("fa", "far");
                        });
                        return;
                    }

                    notificationCounters.forEach((counter) => {
                        counter.removeAttribute("hidden");
                        counter.innerText = unreadCount;
                    });

                    notificationIcons.forEach((icon) => {
                        icon.classList.replace("far", "fa");
                    });
                });
            });
    }

    static toggleAllRead() {
        return API.request('/notifications/read/all', 'PUT')
            .then(API.handleDefaultResponse)
            .then(() => {
                document.querySelectorAll('#notifications-list .notification').forEach((notification) => {
                    notification.classList.remove('unread');
                    notification.querySelector('.toggleReadState i').classList.replace('fa-envelope', 'fa-envelope-open');
                });

                //then reload the unread count, if there are new notifications
                Notification.refreshUnreadCount();
            });
    }
}

let elementNotificationsToggle = document.getElementById('notifications-toggle');
if (elementNotificationsToggle) {
    //If element exists: user is probably signed in -> load and display count of unread notifications
    Notification.refreshUnreadCount();
    //Then refresh the unread count every 30 seconds
    setInterval(Notification.refreshUnreadCount, 30000);
}
