import _ from 'lodash';
import { Follow } from './api/Follow';
import { trans } from 'laravel-vue-i18n';

document.querySelectorAll('.status .like').forEach(likeButton => {
    likeButton.addEventListener('click', pointerEvent => {
        if (!pointerEvent.target.attributes.href.value === '#') {
            //Unauthenticated users should not like the status
            return;
        }

        let statusId = pointerEvent.srcElement.closest('.status').dataset.trwlId;

        let spanLikeCount = document.querySelector(".status[data-trwl-id='" + statusId + "'] .likeCount");

        event.preventDefault();
        event.stopPropagation();

        if (pointerEvent.target.className.includes('like far fa-star')) {
            Status.like(statusId).then(response => {
                if (!response.ok) {
                    if (response.status === 429) {
                        const reset = response.headers.get('X-RateLimit-Reset');
                        let message = trans('messages.too-many-likes');
                        if (reset) {
                            message = message + ' ' + trans('messages.retry-in', { minutes: (reset / 60).toFixed(0) });
                        }
                        notyf.error(message);
                    }
                    return;
                }

                pointerEvent.target.classList.remove('far');
                pointerEvent.target.classList.add('fas');
                pointerEvent.target.classList.add('animated');
                pointerEvent.target.classList.add('bounceIn');
                response.json().then(data => {
                    let likeCount = data.data.count;
                    spanLikeCount.innerText = likeCount;
                    if (likeCount === 0) {
                        spanLikeCount.classList.add('d-none');
                    } else {
                        spanLikeCount.classList.remove('d-none');
                    }
                });
            });
            return;
        }

        Status.unlike(statusId).then(response => {
            if (!response.ok) {
                return;
            }
            const peaches = pointerEvent.target.className.includes('peach');
            pointerEvent.target.className = `like far fa-star ${peaches ? 'peach' : ''}`;

            response.json().then(data => {
                let likeCount = data.data.count;
                spanLikeCount.innerText = likeCount;
                if (likeCount === 0) {
                    spanLikeCount.classList.add('d-none');
                } else {
                    spanLikeCount.classList.remove('d-none');
                }
            });
        });
    });
});

const followButtons = document.querySelectorAll('.follow');
followButtons.forEach(followButton => {
    followButton.addEventListener('click', event => {
        event.preventDefault();
        let userId = event.target.dataset['userid'];
        let privateProfile = event.target.dataset['private'] === 'yes';
        let following = event.target.dataset['following'] === 'yes';

        if (!following) {
            Follow.create(userId).then(response => {
                if (response.ok) {
                    event.target.dataset['following'] = 'yes';
                    event.target.classList.add(privateProfile ? 'disabled' : 'btn-danger');
                    event.target.classList.remove('btn-primary');
                    event.target.innerText = window.translUnfollow;
                }
            });
        } else {
            Follow.destroy(userId).then(response => {
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

document.querySelectorAll('.disconnect').forEach(button => {
    button.addEventListener('click', async event => {
        event.preventDefault();

        const provider = event.target.dataset.provider;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch(urlDisconnect, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ provider }),
            });

            if (response.ok) {
                location.reload();
            } else {
                const errorText = await response.text();
                notyf.error(errorText);
            }
        } catch (error) {
            notyf.error('Ein unerwarteter Fehler ist aufgetreten.');
            console.error('Fetch-Error:', error);
        }
    });
});

const shareButtons = document.querySelectorAll('.trwl-share');
shareButtons.forEach(shareButton => {
    shareButton.addEventListener('click', event => {
        event.preventDefault();

        let shareText = getDataset(event).trwlShareText;
        let shareUrl = getDataset(event).trwlShareUrl;

        if (navigator.share) {
            navigator
                .share({
                    title: 'Träwelling',
                    text: shareText,
                    url: shareUrl,
                })
                .catch(console.error);
        } else {
            navigator.clipboard.writeText(shareText + ' ' + shareUrl).then(() => {
                window.notyf.success('Copied to clipboard');
            });
        }
    });
});

function getDataset(event) {
    let target = event.target.dataset;
    let parent = event.target.parentElement.dataset;

    return _.size(event.target.dataset) ? target : parent;
}
