document.getElementById("export-nominal").addEventListener("click", function () {
    resetFields();
    selectNominal();
})

document.getElementById("export-all").addEventListener("click", function () {
    selectAll();
})

document.getElementById("export-tags").addEventListener("click", function () {
    resetFields();
    selectNominal();
    selectFromArray(["status_tags"]);
});

function selectAll() {
    let select = document.getElementById("export-select");

    for (let i = 0; i < select.options.length; i++) {
        select.options[i].selected = true;
    }
}

function resetFields() {
    let select = document.getElementById("export-select");

    for (let i = 0; i < select.options.length; i++) {
        select.options[i].selected = false;
    }
}

function selectNominal() {
    const nominal = ["status_id", "line_name", "origin_name", "departure_planned", "destination_name", "arrival_planned", "distance", "points", "body"];

    selectFromArray(nominal);
}

function selectFromArray(array) {
    for (let i = 0; i < array.length; i++) {
        let select = document.getElementById("export-select");
        let option = select.querySelector(`option[value="${array[i]}"]`);
        if(option === null) {
            continue;
        }

        option.selected = true;
    }
}
