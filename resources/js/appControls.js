import { Follow } from './api/Follow';

const followButtons = document.querySelectorAll('.follow');
followButtons.forEach((followButton) => {
    followButton.addEventListener('click', (event) => {
        event.preventDefault();
        let userId = event.target.dataset['userid'];
        let privateProfile = event.target.dataset['private'] === 'yes';
        let following = event.target.dataset['following'] === 'yes';

        if (!following) {
            Follow.create(userId).then((response) => {
                if (response.ok) {
                    event.target.dataset['following'] = 'yes';
                    event.target.classList.add(privateProfile ? 'disabled' : 'btn-danger');
                    event.target.classList.remove('btn-primary');
                    event.target.innerText = window.translUnfollow;
                }
            });
        } else {
            Follow.destroy(userId).then((response) => {
                if (response.ok) {
                    if (privateProfile) {
                        location.reload();
                    }
                    event.target.dataset['following'] = 'no';
                    event.target.classList.add('btn-primary');
                    event.target.classList.remove('btn-danger');
                    event.target.innerText = window.translFollow;
                }
            });
        }
    });
});
