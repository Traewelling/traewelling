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
    })
    .then(() => { // After the notification rows have been created, we can add eventlisteners for them
        Array.from(document.getElementsByClassName('toggleReadState')).forEach(btn =>
            btn.addEventListener('click', () => {

                fetch('/notifications/toggleReadState/' + btn.dataset.id, {
                    credentials: 'same-origin',
                    method: 'POST',
                    body: JSON.stringify({}),
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                })
                    .then(res => {
                        const icon = btn.getElementsByTagName('i')[0];
                        const row = document.getElementById('notification-' + btn.dataset.id);

                        if (res.status == 201) { // new state = read
                            icon.classList.replace('fa-envelope', 'fa-envelope-open');
                            row.classList.remove('unread');
                        } else if (res.status == 202) { // new state = unread
                            icon.classList.replace('fa-envelope-open', 'fa-envelope');
                            row.classList.add('unread');
                        }
                    });
            })
        );
    });