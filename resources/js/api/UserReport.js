'use strict';

window.UserReport = class UserReport {

    static reportUser(userId, message) {
        return API.request('/user/' + userId + '/report', 'POST', {message: message})
            .then((response) => response.json())
            .then((data) => {
              if(data.data) {
                notyf.success(data.data);
                return;
              }
              notyf.error(data.message ?? 'An unknown error occured');
          });
    }
}
