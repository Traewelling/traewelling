fetch('/notifications/latest')
    .then(res => res.json())
    .then(notifications => {
        if (notifications.length == 0) return;
        document.getElementById('notifications-empty').classList.add('d-none');

        var html = notifications.reduce((sum, add) => {
            return sum + add.html;
        }, "");

        document.getElementById('notifications-list').insertAdjacentHTML('afterbegin', html);
    });