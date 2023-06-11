// TODO: make this less redundant

function getDarkMode() {
    // use system default if no preference is set
    if (localStorage.getItem("darkMode") == null) {
        localStorage.setItem(
            "darkMode",
            window.matchMedia("(prefers-color-scheme: dark)").matches
        );
    }

    return localStorage.getItem("darkMode") === "true";
}

function setDarkMode(darkMode) {
    localStorage.setItem("darkMode", darkMode);
}

function updateDarkMode() {
    if (getDarkMode()) {
        document.getElementById("colorModeOptionsLight").checked = false;
        document.getElementById("colorModeOptionsDark").checked = true;
        document.documentElement.classList.add("dark");
    } else {
        document.getElementById("colorModeOptionsLight").checked = true;
        document.getElementById("colorModeOptionsDark").checked = false;
        document.documentElement.classList.remove("dark");
    }
}

getDarkMode();
updateDarkMode();

document.getElementsByName("colorModeOptions").forEach((element) => {
    console.log(element.value);
    element.addEventListener("change", (event) => {
        setDarkMode(event.target.value === "dark");
        updateDarkMode();
    });
});
