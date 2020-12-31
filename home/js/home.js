!function ($) {
  $(function(){
    $('#myCarousel').carousel({interval : 10000});
    $("#sendmail").click(function(){ return sendmail(); });
    $(".btndemo").click(function(){ $('#demoModal').modal('show'); });
    $(".brand").click(function(){ showHome(); });
    $('#twitter').load('../wrapper/twitter.php');
    $('#footrss').load('../wrapper/rss_parseur.php');
  })
}(window.jQuery)


sendmail = function() {
  $.ajax({
    type: "POST",
    data: "controle="+$("#controle").val()+"&email="+$("#email").val()+"&nom="+$("#nom").val()+"&sujet="+$("#sujet").val()+"&message="+$("#message").val(),
    url: "contacter_do.php",
    success: function(data)
    {
      var tmp = data.split('||');
      if (tmp[0] == 1)
        $('#contact').html(tmp[1]);
      else
        alert(tmp[1]);
    }
  });

  return false;
}

showHome = function () {
  $('.stdPanel').show();
  $('.oPanel').hide();
}

showFeature = function (mypanel) {
  $('.stdPanel').hide();
  $('.oPanel').hide();
  $(mypanel).show();
}
