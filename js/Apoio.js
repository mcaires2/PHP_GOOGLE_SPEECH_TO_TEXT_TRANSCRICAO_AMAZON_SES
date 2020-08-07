

function PedirSenhaAutenticacao(){

    
    RetomarEstadoOriginal();
    RemoveEstruturaSenha();
    
    var divSenha = $("<div id='idcontainersenha'><label id='labelsenha' for='idsenha'>Senha:</label><input id='idsenha' type='password'  name='password' size='40'  required placeholder='Digite a senha para prosseguir upload do arquivo'><input  id = 'botaoconfirmacao2' class='uploadLuz' type='button' value='ConfirmarUpload' onclick='ajaxUploadArquivo()'></div>");
    $("#botaoconfirmacao").after(divSenha);

    


}






function RemoveEstruturaSenha(){
    
    $("#idcontainersenha").remove();
    $("#idsenha").remove();
    $("#labelsenha").remove();
   // $("#idcontainersenhaconfirmacao").remove();
   // $("#botaoconfirmacao2").remove();
   


}



function RetomarEstadoOriginal(){

    var controle_remove_mensagens_anteriores =  parseInt($("#contador").text());
    
    $("#idcontainerfeedback").remove();
    $("#idcontainerMensagens").remove();
    //$("#link").remove();


    $("#idcontainerFeedback2").text('');
    $("#idcontainerFeedback3").text('Feedback Transcrição');

    $("#uploadLuzForm").css("display", "none");
    $("#uploadLuzTranscrição").css("display", "none");
    $("#botao3").css("display", "none");
    $("#link").css("display", "none");

    
    $("#uploadLuzFormAudio").css("opacity",1);
    $("#uploadLuzForm").css("opacity",1);
    $("#uploadLuzTranscrição").css("opacity",1);

   
        for (var i = 0; i <= controle_remove_mensagens_anteriores; i++) {
            $("#idcontainerMensagens" + (i + 0)).remove();
        }
  
    
   
}


$(document).ready(function () {
    $("#botaoEscolhaArquivo").click(function () {
       
        RetomarEstadoOriginal();
        RemoveEstruturaSenha();


    });
});

