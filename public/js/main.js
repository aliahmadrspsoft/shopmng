function rtl() {
  var body = document.body;
  body.classList.toggle("rtl");
}

function dark() {
  var body = document.body;
  body.classList.toggle("dark");
}




$(document).ready(function () {
  $("ul.a-collapse").click(function () {
    // console.log($(this).hasClass("short"));
    if ($(this).hasClass("short")) {
      $(".a-collapse").addClass("short");
      $(this).toggleClass("short");
      $(".side-item-container").addClass("hide animated");
      $("div.side-item-container", this).toggleClass("hide animated");
    } else {
      $(this).toggleClass("short");
      $("div.side-item-container", this).toggleClass("hide animated");
    }


  });

});

var mixChart = document.getElementById('myChart5');
var mixedChart = new Chart(mixChart, {
  type: 'bar',
  
  data: {
    labels: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد','شهریور', 'مهر', 'آیان', 'آذر', 'دی','بهمن','اسفند'],
      datasets: [{
          label: 'Bar Dataset',
          data: [6,8,5,2,3,10,20,30,40,50],
          backgroundColor: [
            '#ff6384',
            '#4bc0c0',
            '#ffcd56',
            '#c9cbcf',
            '#36a2eb',
            '#ff6384',
            '#4bc0c0',
            '#ffcd56',
            '#c9cbcf',
            '#36a2ea',
            '#36a2ec',
            '#36a2er',
        ]
      }, {
          label: 'Line Dataset',
          data: [8,12,6,3,5,12,20,30,44,60,10,25],

          // Changes this dataset to become a line
          type: 'line'
      }],
  },
  options: {}
});






