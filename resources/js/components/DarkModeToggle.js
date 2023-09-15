// TODO: make this less redundant

function getDarkMode() {
    if (localStorage.getItem("darkMode") === null) {
        localStorage.setItem("darkMode", "auto");
    }

    return localStorage.getItem("darkMode");
}

function setDarkMode(darkMode) {
    localStorage.setItem("darkMode", darkMode);
}

function updateDarkModeMenu(colorMode) {
    let toggleLight = document.getElementById("colorModeToggleLight");
    let toggleDark  = document.getElementById("colorModeToggleDark");
    let toggleAuto  = document.getElementById("colorModeToggleAuto");

    if (!(toggleLight && toggleDark && toggleAuto)) {
        return;
    }

    toggleLight.classList.remove("active");
    toggleDark.classList.remove("active");
    toggleAuto.classList.remove("active");

    if (getDarkMode() === "light") {
        toggleLight.classList.add("active");
    } else if (getDarkMode() === "dark") {
        toggleDark.classList.add("active");
    } else {
        toggleAuto.classList.add("active");
    }
}

function updateDarkMode() {
    let darkModeSetting = getDarkMode();

    if (darkModeSetting === "auto") {
        darkModeSetting = window.matchMedia("(prefers-color-scheme: dark)")
            .matches
            ? "dark"
            : "light";
    }

    if (darkModeSetting === "dark") {
        document.documentElement.classList.add("dark");
    } else {
        document.documentElement.classList.remove("dark");
    }
}

function mountListeners() {
    let toggleLight = document.getElementById("colorModeToggleLight");
    let toggleDark  = document.getElementById("colorModeToggleDark");
    let toggleAuto  = document.getElementById("colorModeToggleAuto");


    if (!(toggleLight && toggleDark && toggleAuto)) {
        return;
    }

    toggleLight.addEventListener("click", () => {
        setDarkMode("light");
        updateDarkModeMenu();
        updateDarkMode();
    });

    toggleDark.addEventListener("click", () => {
        setDarkMode("dark");
        updateDarkModeMenu();
        updateDarkMode();
    });

    toggleAuto.addEventListener("click", () => {
        setDarkMode("auto");
        updateDarkModeMenu();
        updateDarkMode();
    });
}

getDarkMode();
updateDarkModeMenu();
mountListeners();


window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", ({matches}) => {
        if (getDarkMode() === "auto") {
            updateDarkMode(matches ? "dark" : "light");
        }
    });
