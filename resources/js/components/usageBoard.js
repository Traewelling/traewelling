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
    const data = Array.from(JSON.parse(canvas.dataset["json"])).sort(
        (a, b) => a.date > b.date
    );

    
    var config = {
        type: "line",
        data: {
            labels: data.map(data => data.date),
            datasets: canvas.dataset.keys.split(',').map((key, index) => {
                const color = colors[colorindex++ % colors.length];
                console.log(colors);

                return {
                    label: canvas.dataset.title.split(',')[index],
                    data: data.map(date => date[key]),
                    
                    borderColor: color,
                    backgroundColor: `rgba(0,0,0,0)` // transparent
                };
            })
        }
    };

    var ctx = canvas.getContext("2d");
    window.myLine = new Chart(ctx, config);
});
