let delays = document.getElementsByClassName("traindelay");
for (let i = 0; i < delays.length; i++) {
    let delay = delays[i].innerText;
    delay.slice(1);
    if (delay <= 3) {
        delays[i].classList.add("text-success");
    }
    if (delay > 3 && delay < 10) {
        delays[i].classList.add("text-warning");
    }
    if (delay >= 10) {
        delays[i].classList.add("text-danger");
    }
}
