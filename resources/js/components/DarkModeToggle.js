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
    document.getElementById("colorModeToggleLight").classList.remove("active");
    document.getElementById("colorModeToggleDark").classList.remove("active");
    document.getElementById("colorModeToggleAuto").classList.remove("active");

    if (getDarkMode() === "light") {
        document.getElementById("colorModeToggleLight").classList.add("active");
    } else if (getDarkMode() === "dark") {
        document.getElementById("colorModeToggleDark").classList.add("active");
    } else {
        document.getElementById("colorModeToggleAuto").classList.add("active");
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

getDarkMode();
updateDarkModeMenu();

document
    .getElementById("colorModeToggleLight")
    .addEventListener("click", () => {
        setDarkMode("light");
        updateDarkModeMenu();
        updateDarkMode();
    });

document.getElementById("colorModeToggleDark").addEventListener("click", () => {
    setDarkMode("dark");
    updateDarkModeMenu();
    updateDarkMode();
});

document.getElementById("colorModeToggleAuto").addEventListener("click", () => {
    setDarkMode("auto");
    updateDarkModeMenu();
    updateDarkMode();
});

window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", ({ matches }) => {
        if (getDarkMode() === "auto") {
            updateDarkMode(matches ? "dark" : "light");
        }
    });
