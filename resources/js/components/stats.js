import ApexCharts from 'apexcharts';

const chartFavouriteTypes   = document.querySelector("#chart_favourite_types");
const chartPurpose          = document.querySelector("#chart_purpose");
const chartCompanies        = document.querySelector("#chart_companies");
const chartTripTimeCalendar = document.querySelector("#chart_triptime_calendar");

if (chartFavouriteTypes) {
    new ApexCharts(chartFavouriteTypes, {
        chart: {
            type: 'pie'
        }, series: categorySeries, labels: categoryLabels, legend: {
            position: 'bottom'
        }, tooltip: {
            y: {
                formatter: function (value) {
                    return value + categoryMinutes;
                }
            }
        },
    }).render();
}

if (chartPurpose) {
    new ApexCharts(chartPurpose, {
        chart: {
            type: 'pie'
        }, series: chartPurposeSeries, labels: chartPurposeLabels, legend: {
            position: 'bottom'
        }, tooltip: {
            y: {
                formatter: function (value) {
                    return value + chartPurposeMinutes;
                }
            }
        },
    }).render();
}

if (chartCompanies) {
    new ApexCharts(chartCompanies, {
        chart: {
            type: 'pie'
        }, series: chartCompaniesSeries, labels: chartCompaniesLabels, legend: {
            position: 'bottom'
        }, tooltip: {
            y: {
                formatter: function (value) {
                    return value + chartCompaniesMinutes;
                }
            }
        },
    }).render();
}

if (chartTripTimeCalendar) {

    new ApexCharts(document.querySelector("#chart_triptime_calendar"), {
        series: [
            {
                name: chartTripTimeCalendarName,
                data: chartTripTimeCalendarData,
            }
        ],
        chart: {
            type: 'area',
            stacked: false,
            height: 350,
            zoom: {
                type: 'x',
                enabled: true,
                autoScaleYaxis: true
            },
            toolbar: {
                autoSelected: 'zoom'
            }
        },
        dataLabels: {
            enabled: false
        },
        markers: {
            size: 0,
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0,
                stops: [0, 90, 100]
            },
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return value + chartTripTimeCalendarMinutes;
                }
            },
        },
        xaxis: {
            type: 'datetime',
            labels: {
                datetimeUTC: false,
                datetimeFormatter: {
                    year: 'yyyy',
                    month: 'MMM \'yy',
                    day: 'dd MMM',
                    hour: 'HH:mm'
                }
            }
        },
        tooltip: {
            shared: false,
            x: {
                format: 'dd MMM yyyy HH:mm'
            }
        }
    }).render();
}
