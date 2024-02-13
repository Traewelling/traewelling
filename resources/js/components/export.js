// Event Listener
document.getElementById("export-nominal")?.addEventListener("click", function () {
    resetFields();
    selectNominal();
})

document.getElementById("export-all")?.addEventListener("click", function () {
    selectAll();
})

document.getElementById("export-tags")?.addEventListener("click", function () {
    resetFields();
    selectNominalAndTags();
});

document.querySelector('select[name="columns[]"]')?.addEventListener('change', function (e) {
    if (e.target.selectedOptions.length > 8) {
        showWarning();
    } else {
        showWarning(false);
    }
});

// Warning function
function showWarning(show = true) {
    const classList = document.querySelector('#alert-pdf-count').classList;

    if (show) {
        classList.remove('d-none');
    } else {
        classList.add('d-none');
    }
}

// Select functions
function selectAll() {
    let select = document.getElementById("export-select");

    for (const element of select?.options) {
        element.selected = true;
    }
}

function selectNominal() {
    const nominal = ["status_id", "line_name", "origin_name", "departure_planned", "destination_name", "arrival_planned", "distance", "points", "body"];
    showWarning(false);

    selectFromArray(nominal);
}

function selectNominalAndTags() {
    selectNominal()
    selectFromArray(["status_tags"]);
    showWarning();
}

// Helper functions
function resetFields() {
    let select = document.getElementById("export-select");

    for (const element of select.options) {
        element.selected = false;
    }
    showWarning(false);
}

function selectFromArray(array) {
    for (const element of array) {
        let select = document.getElementById("export-select");
        let option = select.querySelector(`option[value="${element}"]`);
        if (option === null) {
            continue;
        }

        option.selected = true;
    }
}
