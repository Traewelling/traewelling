import API from "../api/api";
window.Status = class Status {

    static destroy(statusId) {
        API.request(`/status/${statusId}`, 'delete')
            .then(API.handleDefaultResponse)
            .then(() => {
                //delete status card if present
                let statusCard = document.getElementById(`status-${statusId}`);
                if (statusCard) {
                    statusCard.remove();
                }

                //redirect to dashboard, if user is on status page which is deleted
                if (window.location.pathname === `/status/${statusId}`) {
                    window.location.href = '/dashboard';
                }
            });
    }

    static like(statusId) {
        return API.request(`/status/${statusId}/like`, 'POST');
    }

    static unlike(statusId) {
        return API.request(`/status/${statusId}/like`, 'DELETE');
    }
}
