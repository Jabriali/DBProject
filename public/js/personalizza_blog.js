$(".cancella_couatore").click(function (e) {
  e.preventDefault();
  let coautore = $(this).attr("id");
  let blog_id = $(this).attr("href");
  
  $.ajax({
  type: "POST",
  url: "personalizza_blog.php?id="+blog_id,
  data: {"rimuoviCoautore": coautore},
  dataType: "json",
  success: function(){
    $("#result").html(response);
  }
  });

  $("#"+coautore).css("display", "none");
  $("."+coautore+"-span").css("display", "none");
});

$(".cancella_categoria-principale").click(function (e) {
  e.preventDefault();
  let categoria = $(this).attr("id");
  let blog_id = $(this).attr("href");
  
  $.ajax({
  type: "POST",
  url: "personalizza_blog.php?id="+blog_id,
  data: {"rimuoviCategoria": categoria},
  dataType: "json",
  success: function(){
    $("#result").html(response);
  }
  });

  $(".categoria-container-"+categoria).css("display", "none");
  $(".scegli-categoria").addClass("d-none");
  $("#associa").addClass("d-none");
  setInterval('location.reload()', 500);
});

$(".cancella_categoria-secondaria").click(function (e) {
  e.preventDefault();
  let categoria = $(this).attr("id");
  let blog_id = $(this).attr("href");
  
  $.ajax({
  type: "POST",
  url: "personalizza_blog.php?id="+blog_id,
  data: {"cancellaSottocategoria": true},
  dataType: "json",
  success: function(){
    $("#result").html(response);
  }
  });

  $(".categoria-container-"+categoria).css("display", "none");
  $("#associa").addClass("d-none");
  setInterval('location.reload()', 500);
});


