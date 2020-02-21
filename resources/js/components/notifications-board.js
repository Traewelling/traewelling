fetch('/notifications/latest', {
    credentials: 'same-origin'
})
    .then(res => res.json())
    .then(notifications => {
        // If there are no notifications, we can just quit here. Else, show the items.
        if (notifications.length == 0) return;
        document.getElementById('notifications-empty').classList.add('d-none');

        // if there are unread notifications, make the bell ring.
        if (notifications.some(n => n.read_at == null)) {
            Array.from(document.getElementsByClassName('notifications-bell'))
                .forEach(bell => bell.classList.replace("far", "fa"));
        }

        var html = notifications.reduce((sum, add) => {
            return sum + add.html;
        }, "");

        document.getElementById('notifications-list').insertAdjacentHTML('afterbegin', html);
    });