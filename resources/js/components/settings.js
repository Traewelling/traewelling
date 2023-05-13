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
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": token
            }
        });

        $.ajax({
            url: urlAvatarUpload,
            type: "POST",
            data: {image: img},
            success: function (data) {
                // Bestehendes Bild noch ändern
                $("#theProfilePicture").attr("src", img);
                $("#uploadAvatarModal").modal("hide");
                $("#deleteProfilePictureButton").removeClass("d-none");
            },
            error: function () {
                $("#upload-error").removeClass("d-none");
            }
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
}
