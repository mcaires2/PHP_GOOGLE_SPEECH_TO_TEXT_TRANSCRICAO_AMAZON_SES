<!DOCTYPE html>
<html lang="pt">

<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <title>Speech to Text</title>
        <!-- <link rel="stylesheet" href="css/Coordenadas.css"> -->
        <link rel="stylesheet" href="css/upload.css">
        <link rel="stylesheet" href="css/uploadMediaQuery.css">
</head>

<body>
       <div id="contador" hidden>0</div>
        
        <form id="uploadLuzFormAudio" action="SpeechToText.php" method="POST" enctype="multipart/form-data">
                <fieldset id="uploadarquivo">
                        <Legend id="uploadarquivolg">Upload Arquivo Audio /Vídeo Speech to Text - Formatos Entrada .mp3,.m4a, .mp4, .webm, .mov - por Marcos Luz</Legend>
                        <input id ="botaoEscolhaArquivo" type="file" required name="Arquivo" accept=".m4a,.mp3,.mp4,.webm,.mov" size="200" maxlenght="300">
                        <input  id = 'botaoconfirmacao' class="uploadLuz" type="button" value="FazerUploadArquivoAudio" onclick="PedirSenhaAutenticacao()">
                </fieldset>
        </form>





        <form id="uploadLuzForm">
                <fieldset id="coordenadasfield">
                        <Legend id="coordenadasfieldlegend"> Sincronização e Transcrição Speech to Text - por Marcos Luz</Legend>
                        <div id="idcontainerFeedback2"></div>
                        <div id='idcontainerbotao'><input id="botao" type="button" value="Iniciar Requisição Transcrição"
                             onclick="UploadBloco2Transcricao()">
                        </div>
                        
                </fieldset>

        </form>



        <div id="uploadLuzTranscrição">
                <fieldset id="coordenadasfield">
                        <Legend id="coordenadasfieldlegend">Transcrição - por Marcos Luz</Legend>
                        <div id="idcontainerFeedback3">Feedback Transcrição</div>
                        <div id='idcontainerbotao'>
                                <a id='link'><button id="botao3" type="button">Download_Arquivo_Word</button></a>
                        </div>
                        
                </fieldset>

        </div>


        <script src="js/Apoio.js"></script>
        <script src="js/chamarTranscricao.js"></script>

</body>

</html>



