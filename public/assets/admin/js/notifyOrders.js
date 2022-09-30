

"use strict";
//notify('success', 'Prueba');
var x = document.getElementById("myAudio"); 

ordersToday()

setInterval(function () {
    ordersToday()
}, 300000);

function ordersToday() {
  $.ajax({
    headers: {
      "X-CSRF-TOKEN": "{{ csrf_token() }}",
    },
    url: homeUrl + "/admin/orders/notifyorders",
    method: "GET",
    success: function (result) {
      if (result > 0) {
        notify("success", "Si existen pedidos pendientes");
        var audio = new Audio(
            soundUrl
        );
        audio.play();
        /*const audio = new Audio("https://freesound.org/data/previews/501/501690_1661766-lq.mp3");
        audio.play();*/
      }
    },
  });
}

function playAudio() { 
    $("audio").trigger("play");
  } 
