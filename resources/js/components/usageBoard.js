let timeFormat = "YYYY-MM-DD";

const colors = [
	'rgb(255, 99, 132)',
	'rgb(255, 159, 64)',
	'rgb(255, 205, 86)',
	'rgb(75, 192, 192)',
	'rgb(54, 162, 235)',
	'rgb(153, 102, 255)',
	'rgb(201, 203, 207)'
];

let colorindex = 0;

Array.from(document.getElementsByClassName("date-canvas")).forEach((canvas) => {
    const labels = JSON.parse(canvas.dataset.labels);
    const data = JSON.parse(canvas.dataset.json);

    var config = {
        type: "line",
        data: {
            labels: labels,
            datasets: canvas.dataset.keys.split(',').map((key, index) => {
                const color = colors[colorindex++ % colors.length];

                return {
                    label: canvas.dataset.title.split(',')[index],
                    data: data.map(date => typeof date[key] == "undefined" ? 0 : date[key]),

                    borderColor: color,
                    backgroundColor: `rgba(0,0,0,0)` // transparent
                };
            })
        }
    };

    var ctx = canvas.getContext("2d");
    window.myLine = new Chart(ctx, config);
});
