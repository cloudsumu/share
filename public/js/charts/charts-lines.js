$.ajax({
    url: "/admin/getUserRequestStatus", 
    success: function(result){
        var today = new Date();

        let cancelled_result = result['CANCELLED'];
        let completed_result = result['COMPLETED'];

        // getting dates
        var time = [
            today.getFullYear()+"-01",
            today.getFullYear()+"-02",
            today.getFullYear()+"-03",
            today.getFullYear()+"-04",
            today.getFullYear()+"-05",
            today.getFullYear()+"-06",
            today.getFullYear()+"-07",
            today.getFullYear()+"-08",
            today.getFullYear()+"-09",
            today.getFullYear()+"-10",
            today.getFullYear()+"-11",
            today.getFullYear()+"-12",
        ];

        var index = 0;
        var data = [];
        var cancelled = [];
        var completed = [];

        if(cancelled_result.length) {
            var i = 0;

            // Try to merge both the for loops to accommodate for gaping months in middle. This only takes care of starting skips.
            for(i; i < 12; i++) {
                // Get time to correct position as the response.
                if(time[i] == cancelled_result[0].duration) {
                    break;
                }
                cancelled[i] = 0;
            }
            for(i; i < 12; i++) {
                if(index <= cancelled_result.length-1 && cancelled_result[index].duration == time[i]) {
                    cancelled[i] = cancelled_result[index].cancelled_requests;
                    index++;
                }
                else {
                    cancelled[i] = 0;
                }
            }
        }

        index = 0;
        if(completed_result.length) {
            var i = 0;
            for(i; i < 12; i++) {
                // Get time to correct position as the response.
                if(time[i] == completed_result[0].duration) {
                    break;
                }
                completed[i] = 0;
            }
            for(i; i < 12; i++) {
                if(index <= completed_result.length-1 && completed_result[index].duration == time[i]) {
                    completed[i] = completed_result[index].completed_requests;
                    index++;
                }
                else {
                    completed[i] = 0;
                }
            }
        }

        option = {
            maintainAspectRatio: false,
            responsive: true,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    time: {
                    unit: 'date'
                    },
                    gridLines: {
                    display: false,
                    drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                    maxTicksLimit: 5,
                    padding: 10,
                    callback: function(value, index, values) {
                        return number_format(value);
                    }
                    },
                    gridLines: {
                    color: "rgb(234, 236, 244)",
                    zeroLineColor: "rgb(234, 236, 244)",
                    drawBorder: false,
                    borderDash: [2],
                    zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': ' + number_format(tooltipItem.yLabel);
                    }
                }
            }
        };

        Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        var ctx = document.getElementById("line");
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Cancelled Requests",
                    lineTension: 0.3,
                    backgroundColor: "rgba(6, 148, 162, 0.05)", // yellowish
                    borderColor: "rgba(6, 148, 162, 1)",
                    defaultFontColor: 'orange',
                    pointRadius: 3,
                    Color: "rgba(6, 148, 162, 1)",
                    pointBackgroundColor: "rgba(6, 148, 162, 1)",
                    pointBorderColor: "rgba(6, 148, 162, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(6, 148, 162, 1)",
                    pointHoverBorderColor: "rgba(6, 148, 162, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    // This is the data that we have to set.
                    data: cancelled,
                },
                {
                    label: "Completed Requests",
                    lineTension: 0.3,
                    backgroundColor: "rgba(0, 255, 0, 0.05)",
                    borderColor: "rgba(0, 255, 0, 1)",
                    defaultFontColor: 'orange',
                    pointRadius: 3,
                    Color: "rgba(0, 255, 0, 1)",
                    pointBackgroundColor: "rgba(0, 255, 0, 1)",
                    pointBorderColor: "rgba(0, 255, 0, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(0, 255, 0, 1)",
                    pointHoverBorderColor: "rgba(0, 255, 0, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    // This is the data that we have to set.
                    data: completed,
            }],
            },
            options: option
        });
    },
    type: 'get',
});