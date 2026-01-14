import Croppie from 'croppie/croppie';
import API from '../api/api';

const uploadButton = document.getElementById('upload-button');
const uploadDemo   = document.getElementById('upload-demo');
if (uploadButton && uploadDemo) {
    let resize = new Croppie(uploadDemo, {
        enableExif: true,
        enableOrientation: true,
        viewport: {
            width: 400,
            height: 400,
            type: 'square',
        },
        boundary: {
            width: 400,
            height: 400,
        },
    });

    const image = document.getElementById('image');
    image.addEventListener('change', function () {
        uploadButton.classList.remove('d-none');
        uploadDemo.classList.remove('d-none');

        let reader    = new FileReader();
        reader.onload = function (e) {
            const url = e.target.result;
            resize.bind({ url });
        };
        reader.readAsDataURL(this.files[0]);
    });

    document.querySelector('.upload-image').addEventListener('click', function () {
        resize.result({
            type: 'canvas',
            size: 'viewport',
        }).then(function (img) {
            Settings.uploadProfilePicture(img)
                .then(() => {
                    document.getElementById('theProfilePicture').src = img;
                    document.getElementById('btnModalDeleteProfilePicture')?.classList.remove('d-none');
                })
                .catch(function (error) {
                    if (error.status === 403) {
                        notyf.error('Forbidden: You are not allowed to upload a profile picture.');
                    } else {
                        notyf.error('An error occured while uploading the profile picture.');
                    }
                });
        });
    });
}

window.Settings = class Settings {
    static deleteProfilePicture() {
        API.request('/settings/profilePicture', 'delete')
            .then(API.handleDefaultResponse)
            .then(() => {
                //Remove delete-btn if existing
                let btnModalDeleteProfilePicture = document.getElementById('btnModalDeleteProfilePicture');
                btnModalDeleteProfilePicture?.remove();

                //Show default profile picture
                let theProfilePicture = document.getElementById('theProfilePicture');
                theProfilePicture?.setAttribute('src', '/img/user.png');
            });
    }

    static uploadProfilePicture(image) {
        return API.request('/settings/profilePicture', 'POST', { image: image })
            .then(API.handleDefaultResponse);
    }
};
