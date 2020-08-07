

////////////////////////////////////




var password1
function ajaxUploadArquivo() {

    RetomarEstadoOriginal()

    password1 = $('#idsenha').val()
    RemoveEstruturaSenha();

    var divSenha = $("<div id='idcontainerfeedback'><div>Upload do Arquivo agendado, aguarde...</div>");
    $("#botaoconfirmacao").after(divSenha);
    $("#uploadLuzFormAudio").submit()


}

var feedbackNomeArquivoServer;
var NomeArquivoUpload;
var passphrase;

$(document).ready(function (e) {
    $("form#uploadLuzFormAudio").on('submit', (function (e) {
        e.preventDefault();

        //var formData = new FormData(this);

        var formData = new FormData(this)
        var password = password1
        formData.append("password", password)   // tenho de construir o formulário antes de passar para o server side


        $.ajax({
            url: "ajax/ajaxUpload.php",
            //data: new FormData(this),
            //data:{password:password},
            data: formData,
            type: "POST",
            contentType: false,
            //cache: false,
            processData: false,
            beforeSend: function () {
                $("#idcontainerfeedback").text("Upload do Arquivo iniciado, aguarde...")

            },
            success: function (JsonDados) {
                var StringDados = JSON.parse(JsonDados)
                var feedbackString = StringDados[0];
                feedbackNomeArquivoServer = StringDados[1]; //global
                var feedbackResultadoOperacaoUpload = StringDados[2];
                $("#idcontainerfeedback").text(feedbackString);

                if (feedbackResultadoOperacaoUpload == 1) {


                    NomeArquivoUpload = StringDados[3]; // global
                    passphrase = StringDados[4];        // global

                    $("#uploadLuzForm").css("display", "block");
                    $("#uploadLuzFormAudio").css("opacity", 0.55);
                    $("#idcontainerFeedback2").text('Job ID {' + feedbackNomeArquivoServer + '} pronto para iniciar sincronização e transcrição');


                }

            }
        });
    }));
});


///// fim do upload p server





function UploadBloco2Transcricao() {

    $("#idcontainerMensagens").remove();

    var divCriar = $("<div id='idcontainerMensagens'><div>Sincronização Job com Bucket Iniciada...</div>");
    $("#idcontainerbotao").after(divCriar);



    $.ajax({
        url: "ajax/ajaxUploadBucketCloud.php",
        data: { feedbackNomeArquivoServer: feedbackNomeArquivoServer, NomeArquivoUpload: NomeArquivoUpload, passphrase: passphrase },

        type: "POST",
        beforeSend: function () {
            $("#idcontainerMensagens").text("Sincronização Job com Bucket requisitada ao servidor...")

        },
        success: function (JsonDados) {
            var StringDados = JSON.parse(JsonDados)
            var feedbackString = StringDados[0];
            var feedbackResultadoOperacaoUploadBucket = StringDados[1];
            $("#idcontainerMensagens").text(feedbackString);

            if (feedbackResultadoOperacaoUploadBucket == 1) {

                $("#uploadLuzTranscrição").css("display", "block");
                $("#uploadLuzForm").css("opacity", 0.55);
                $("#idcontainerFeedback3").text('Job ID {' + feedbackNomeArquivoServer + '} em análise, aguardando retorno de dados contendo transcrição...');

                ajaxTranscricaoCloud()



            }

        }
    });



    // abrir o trabalho no bucket e aguardar feedback
    //  esperar a transcrição e armazenar num array
    // do array abrir um phpword e descarregar o conteúdo e salvar como o mesmo nome do arquivopur.doc
    // gerar um bloco próprio para download do doc...


}


/// fim do upload Bucket
var contador =0;
function ajaxTranscricaoCloud() {

    var feedbackNomeArquivoServer1 = feedbackNomeArquivoServer;
    var NomeArquivoUpload1 = NomeArquivoUpload;
    var NomeArquivoPuro = NomeArquivoUpload1.substring(0,NomeArquivoUpload1.length -4)
    var passphrase1 = passphrase


    feedbackNomeArquivoServer = '';
    NomeArquivoUpload = '';
    passphrase = '';


    $.ajax({
        url: "ajax/Transcricao.php",
        data: { feedbackNomeArquivoServer1: feedbackNomeArquivoServer1, NomeArquivoUpload1: NomeArquivoUpload1, passphrase1: passphrase1 },

        type: "POST",
        beforeSend: function () {
            $("#idcontainerMensagens").text("Sincronização Job com Bucket requisitada ao servidor...")

        },
        success: function (JsonDados) {
            var StringDados = JSON.parse(JsonDados)
            var txt2 = 'Feedback Transcrição -' + NomeArquivoUpload1;
            contador = 0;
            var txt;
            $("#botao3").css("display", "block");
            $("#link").css("display", "block");
            StringDados.forEach(myFunction);
            function myFunction(value) {
                txt = value;

                var divCriar = $("<div class = mensagens id=idcontainerMensagens" + contador + "><div>Sincronização Job com Bucket Iniciada...</div>");
                contador = contador + 1

                if (contador == 1) {

                    $("#idcontainerFeedback3").after(divCriar);
                    $("#idcontainerMensagens" + (contador - 1)).text(value);

                } else {


                    $("#idcontainerMensagens" + (contador - 2)).after(divCriar);
                    $("#idcontainerMensagens" + (contador - 1)).text(value);


                }

            }

            $("#idcontainerFeedback3").text(txt2);
            $("#contador").text(contador);


            
            $("#link").attr("href", "/002_TranscricaoWord/" +NomeArquivoPuro + ".docx");
         
                               
            
        }

        

    });


}







