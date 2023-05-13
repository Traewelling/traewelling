require("croppie/croppie");

var resize = $("#upload-demo").croppie({
    enableExif: true,
    enableOrientation: true,
    viewport: {
        width: 200,
        height: 200,
        type: "square"
    },
    boundary: {
        width: 300,
        height: 300
    }
});

$("#image").on("change", function () {
    $("#upload-demo").removeClass("d-none");
    $("#upload-button").removeClass("d-none");

    let reader    = new FileReader();
    reader.onload = function (e) {
        resize.croppie("bind", {
            url: e.target.result
        });
    };
    reader.readAsDataURL(this.files[0]);
});

$(".upload-image").on("click", function (ev) {
    resize.croppie("result", {
        type: "canvas",
        size: "viewport"
    }).then(function (img) {
        Settings.uploadProfilePicture(img)
            .then(() => {
                document.getElementById("theProfilePicture").src = img;
                document.getElementById("btnModalDeleteProfilePicture")?.classList.remove("d-none");
            });
    });
});


window.Settings = class Settings {

    static deleteProfilePicture() {
        API.request('/settings/profilePicture', 'delete')
            .then(response => {
                if (!response.ok) {
                    return response.json().then(API.handleGenericError);
                }

                //Remove delete-btn if existing
                let btnModalDeleteProfilePicture = document.getElementById("btnModalDeleteProfilePicture");
                btnModalDeleteProfilePicture?.remove();

                //Show default profile picture
                let theProfilePicture = document.getElementById('theProfilePicture');
                theProfilePicture?.setAttribute('src', `/img/user.png`);

                return response.json().then(data => {
                    notyf.success(data.data.message);
                });
            })
            .catch(API.handleGenericError);
    }

    static uploadProfilePicture(image) {
        return API.request('/settings/profilePicture', 'POST', {image: image})
            .then(response => {
                if (!response.ok) {
                    return response.json().then(API.handleGenericError);
                }

                return response.json().then(data => {
                    notyf.success(data.data.message);
                });
            })
            .catch(API.handleGenericError);
    }
}
