$.ajax({
  url: "/admin/getAdminCredits",
  success: function (result) {
    var today = new Date();

    var time = [
      today.getFullYear() + "-01",
      today.getFullYear() + "-02",
      today.getFullYear() + "-03",
      today.getFullYear() + "-04",
      today.getFullYear() + "-05",
      today.getFullYear() + "-06",
      today.getFullYear() + "-07",
      today.getFullYear() + "-08",
      today.getFullYear() + "-09",
      today.getFullYear() + "-10",
      today.getFullYear() + "-11",
      today.getFullYear() + "-12",
    ];

    var index = 0;
    var data = [];

    if (result.length) {
      var i = 0;

      // Try to merge both the for loops to accommodate for gaping months in middle. This only takes care of starting skips.
      for (i; i < 12; i++) {
        // Get time to correct position as the response.
        if (time[i] == result[0].duration) {
          break;
        }
        data[i] = 0;
      }
      for (i; i < 12; i++) {
        if (index <= result.length - 1 && result[index].duration == time[i]) {
          data[i] = result[index].total;
          index++;
        } else {
          data[i] = 0;
        }
      }
    }

    var ctx = document.getElementById("pie");
    new Chart(ctx, {
      type: "doughnut",
      data: {
        datasets: [
          {
            data: data,
            backgroundColor: [
              "#00c0c7",
              "#5144d3",
              "#e8871a",
              "#da3490",
              "#9089fa",
              "#47e26f",
              "#2780eb",
              "#6f38b1",
              "#dfbf03",
              "#dfbf03",
              "#dfbf03",
              "#dfbf03",
            ],
            label: "Dataset 1",
          },
        ],
        labels: [
          "January",
          "February",
          "March",
          "April",
          "May",
          "June",
          "July",
          "August",
          "September",
          "October",
          "November",
          "December",
        ],
      },
      options: {
        responsive: true,
        cutoutPercentage: 80,
        legend: {
          display: false,
        },
      },
    });
  },
  type: "get",
});
