require("croppie/croppie");

var resize = $("#upload-demo").croppie({
    enableExif: true,
    enableOrientation: true,
    viewport: {
        // Default { width: 100, height: 100, type: 'square' }
        width: 200,
        height: 200,
        type: "circle" //square
    },
    boundary: {
        width: 300,
        height: 300
    }
});

$("#image").on("change", function() {
    $("#upload-demo").removeClass("d-none");
    $("#upload-button").removeClass("d-none");

    var reader = new FileReader();
    reader.onload = function(e) {
        resize
            .croppie("bind", {
                url: e.target.result
            });
    };
    reader.readAsDataURL(this.files[0]);
});

$(".upload-image").on("click", function(ev) {
    resize
        .croppie("result", {
            type: "canvas",
            size: "viewport"
        })
        .then(function(img) {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": token
                }
            });

            $.ajax({
                url: urlAvatarUpload,
                type: "POST",
                data: { image: img },
                success: function(data) {
                    // Bestehendes Bild noch ändern
                    $("#theProfilePicture").attr("src", img);
                    $("#uploadAvatarModal").modal("hide");
                    $("#deleteProfilePictureButton").removeClass("d-none");
                },
                error: function() {
                    $("#upload-error").removeClass("d-none");
                }
            });
        });
});
