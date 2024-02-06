// Hier kommen jetzt die 25 größten Städte Deutschlands rein, damit die Maschine
// schon mal was zum Zeigen hat, auch wenn noch kein AJAX-Request passiert ist.
// Liste: https://de.wikipedia.org/wiki/Liste_der_Gro%C3%9Fst%C3%A4dte_in_Deutschland#Tabelle
const popularStations = [
    "Hamburg Hbf", "Berlin Hbf", "München Hbf", "Köln Hbf", "Frankfurt(Main)Hbf", "Stuttgart Hbf",
    "Düsseldorf Hbf", "Leipzig Hbf", "Dortmund Hbf", "Essen Hbf", "Bremen Hbf", "Dresden Hbf",
    "Hannover Hbf", "Nürnberg Hbf", "Duisburg Hbf", "Bochum Hbf", "Wuppertal Hbf", "Bielefeld Hbf",
    "Bonn Hbf", "Münster Hbf", "Karlsruhe Hbf", "Mannheim Hbf", "Augsburg Hbf", "Wiesbaden Hbf",
    "Mönchengladbach Hbf"
];
(function () {
    const input     = document.getElementById("station-autocomplete");
    const container = document.getElementById("station-autocomplete-container");
    if (input == null) {
        return;
    }

    window.awesomplete = new Awesomplete(input, {
        minChars: 2,
        autoFirst: true,
        sort: false,
        list: popularStations,
        filter: () => true,
        container: function () {
            container.classList.add("awesomplete");
            return container;
        }
    });

    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                func.apply(this, args);
            }, timeout);
        };
    }

    function fetchStations() {
        if (input.value.length < 2) return;

        fetch("/transport/train/autocomplete/" + encodeURI(input.value))
            .then(res => res.json())
            .then(json => {
                window.awesomplete.list = json.map(station => {
                    return {
                        value: station.name,
                        label: station.name + (station.rilIdentifier ? " (" + station.rilIdentifier + ")" : "")
                    };
                });
            });
    }

    const processChange = debounce(() => fetchStations());
    input.addEventListener("keyup", processChange);

})();
