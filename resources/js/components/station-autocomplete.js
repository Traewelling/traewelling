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
    const input = document.getElementById("station-autocomplete");
    const container = document.getElementById("station-autocomplete-container");
    if (input == null) {
        return;
    }

    window.awesomplete = new Awesomplete(input, {
        minChars: 2,
        autoFirst: true,
        list: popularStations,
        container: function (input) {
            container.classList.add("awesomplete");
            return container;
        }
    });
    input.addEventListener("keyup", (event) => {
        if (input.value.length < 5) return;

        // Hier können wir dann auch irgendwann die Flixbus-API einbauen,
        // finds ohne getrackte Flixbusse eher sinnlos.

        // Hier ist nur Bahn-Stuff
        fetch(urlAutocomplete + "/" + encodeURI(input.value))
            .then(res => res.json())
            .then(json => {
                window.awesomplete.list = json.map(d => {
                    return {
                        value: d.name,
                        label: d.name + "",
                    };
                });
            });
    });
})();
