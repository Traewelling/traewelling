const avatarUpload = document.getElementById("avatarUpload");

if (typeof avatarUpload != "undefined") {
    avatarUpload.addEventListener("change", () => {
        document.getElementById("avatarUpload-filename").nodeValue =
            avatarUpload.value;
    });
}
