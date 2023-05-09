window.Status = class Status {

    static destroy(statusId) {
        API.request(`/status/${statusId}`, 'delete')
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        notyf.error(data?.message ?? 'An unknown error occured.');
                    });
                }

                return response.json().then(data => {
                    //delete status card if present
                    let statusCard = document.getElementById(`status-${statusId}`);
                    if (statusCard) {
                        statusCard.remove();
                    }

                    notyf.success(data.data.message);

                    //redirect to dashboard, if user is on status page which is deleted
                    if (window.location.pathname === `/status/${statusId}`) {
                        window.location.href = '/dashboard';
                    }
                });
            })
            .catch(error => {
                console.error(error);
                notyf.error('An unknown error occured.');
            });
    }
}
