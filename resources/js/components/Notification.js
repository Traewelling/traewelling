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

    static markAsRead(notificationId) {
        return API.request(`/notifications/read/${notificationId}`, 'PUT')
            .then(() => {
                document.querySelector('#notifications-list .notification[data-id="' + notificationId + '"]').classList.remove('unread');
                document.querySelector('#notifications-list .notification[data-id="' + notificationId + '"] .toggleReadState i').classList.replace('fa-envelope', 'fa-envelope-open');

                //reduce the unread count by one
                let notificationCounters = document.querySelectorAll('.notifications-pill');
                notificationCounters.forEach((counter) => {
                    counter.innerText = counter.innerText - 1;
                });

                //then reload the unread count, if there are new notifications
                Notification.refreshUnreadCount();
            });
    }

    static markAsUnread(notificationId) {
        return API.request(`/notifications/unread/${notificationId}`, 'PUT')
            .then(() => {
                document.querySelector('#notifications-list .notification[data-id="' + notificationId + '"]').classList.add('unread');
                document.querySelector('#notifications-list .notification[data-id="' + notificationId + '"] .toggleReadState i').classList.replace('fa-envelope-open', 'fa-envelope');

                //increase the unread count by one
                let notificationCounters = document.querySelectorAll('.notifications-pill');
                notificationCounters.forEach((counter) => {
                    counter.innerText = parseInt(counter.innerText) + 1;
                });

                //then reload the unread count, if there are new notifications
                Notification.refreshUnreadCount();
            });
    }

    static latest() {
        return API.request('/notifications');
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

    static getIconForType(type) {
        switch (type) {
            case 'EventSuggestionProcessed':
                return 'fa-regular fa-calendar';
            case 'FollowRequestApproved':
                return 'fas fa-user-plus';
            case 'FollowRequestIssued':
                return 'fas fa-user-plus';
            case 'MastodonNotSent':
                return 'fas fa-exclamation-triangle';
            case 'StatusLiked':
                return 'fas fa-heart';
            case 'UserFollowed':
                return 'fas fa-user-friends';
            case 'UserJoinedConnection':
                return 'fa fa-train';
            default:
                return 'far fa-envelope';
        }
    }

    static getWarntypeForType(type) {
        switch (type) {
            case 'MastodonNotSent':
                return 'warning';
            default:
                return 'neutral';
        }
    }
}

let elementNotificationsToggle = document.getElementById('notifications-toggle');
if (elementNotificationsToggle) {
    //If element exists: user is probably signed in -> load and display count of unread notifications
    Notification.refreshUnreadCount();
    //Then refresh the unread count every 30 seconds
    setInterval(Notification.refreshUnreadCount, 30000);
}

document.querySelectorAll('nav .notifications-board-toggle').forEach(button => {
    button.addEventListener('click', () => {
        Notification.latest()
            .then(function (request) {
                request.json().then(function (json) {
                    let notifications     = json.data;
                    let notificationsList = document.getElementById('notifications-list');

                    notificationsList.innerHTML = '';

                    document.getElementById('notifications-loading').classList.add('d-none');
                    if (notifications.length === 0) {
                        document.getElementById('notifications-empty').classList.remove('d-none');
                        return;
                    }
                    document.getElementById('notifications-empty').classList.add('d-none');

                    //Then render the notifications
                    notifications.forEach((notification) => {
                        let notificationElement = document.createElement('div');
                        notificationElement.classList.add('notification');
                        notificationElement.innerHTML = `
                            <div class="row notification ${Notification.getWarntypeForType(notification.type)} ${!notification.readAt ? 'unread' : ''}" data-id="${notification.id}">
                                <a class="col-1 col-sm-1 align-left lead" href="${notification.link}">
                                    <i class="${Notification.getIconForType(notification.type)}"></i>
                                </a>
                                <a class="col-7 col-sm-8 align-middle" href="${notification.link}">
                                    <p class="lead">${notification.leadFormatted}</p>
                                    ${notification.noticeFormatted ?? ''}
                                </a>
                                <div class="col col-sm-3 text-end">
                                    <button type="button" class="interact toggleReadState">
                                        <span aria-hidden="true"><i class="far ${!notification.readAt ? 'fa-envelope' : 'fa-envelope-open'}"></i></span>
                                    </button>
                                    <div class="text-muted">${notification.createdAtForHumans}</div>
                                </div>
                            </div>
                        `;
                        notificationsList.appendChild(notificationElement);
                    });

                    document.querySelectorAll('#notifications-list .toggleReadState').forEach((button) => {
                        button.addEventListener('click', () => {
                            let notificationId  = button.closest('.notification').dataset.id;
                            let currentlyUnread = button.closest('.notification').classList.contains('unread');
                            if (currentlyUnread) {
                                Notification.markAsRead(notificationId);
                            } else {
                                Notification.markAsUnread(notificationId);
                            }
                        });
                    });
                });
            });
    });
});
