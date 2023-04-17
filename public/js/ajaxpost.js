$('.commento').on('keypress', function(e) {
    var code = e.keyCode || e.which;
    // https://stackoverflow.com/questions/22955975/press-the-enter-key-in-a-text-box-with-jquery
    if(code==13){
        let commento = $(this).val();
        let postid = $(this).attr("id");
        let autore = $(this).attr("name");
        let avatar = $(this).attr("avatar");
        let data = new Date();
        let data_ottimizzata = data.getFullYear() + "-0" + data.getMonth() + "-0" + data.getDay();
        let contatore_commenti = $("#contatore-commenti").text();
    
        $(".commento").css("display", "none");
        contatore_commenti = parseInt(contatore_commenti) + 1;
        $.ajax({
            type: "post",
            url: "post.php?id="+postid,
            data: {"inserisciCommento": [commento, autore, contatore_commenti]},
            dataType: "json",
            success: function(){
              $("#result").html(response);
            }
        });

        $("#lista-commenti").append(
            "<div class='nuovo-commento d-flex flex-column mt-3'> </div>");

        $(".nuovo-commento").append(
            "<div class='info-utente d-flex flex-row'></div>");

        if (!avatar) {
            $(".info-utente").append(
                "<img class='me-3 img-fluid rounded' src='../images/profiloutenti/default.png' style='width: 50px;'>"+
                "<div class='d-flex flex-row align-items-center text-primary'>"+ 
                    autore 
                +"</div>");
        } else {
            $(".info-utente").append("<img class='user-avatar avatar-utente'></img>"+
            "<div class='d-flex flex-row align-items-center text-primary'>" + 
                    autore 
            +"</div>");
            $('.avatar-utente').attr('src', avatar);
        }

        $(".nuovo-commento").append("<div>" + commento + "</div>" + "<div>" + data_ottimizzata + "</div>")
        $("#contatore-commenti").text(parseInt(contatore_commenti)+1);

    
            
    }
});
