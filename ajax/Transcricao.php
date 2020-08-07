<?php
include_once('../db/conexao_marcose.php');
include_once('SES_Email_Amazon_Send.php');
date_default_timezone_set('America/Recife');

ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '256M');
ini_set('max_execution_time', 600);

require_once "../vendor/autoload.php";
include '../vendor/autoload.php';


//
// Imports the Google Cloud client library
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
//


// $arrayDicionarioPalavras=array('word','excel','submeteu');



if(isset($_POST["passphrase1"])) {
    

    $extensao = strripos($_POST["feedbackNomeArquivoServer1"],"."); // encontro last dot
    $NomePuroArquivo = substr($_POST["feedbackNomeArquivoServer1"],0,$extensao); // usar para construir nomefileserve.mp3
    
    
    // credentials and setup para trabalhar com Cloud Google
    
    $password = $_POST["passphrase1"];
    $NomeArquivoServer = $NomePuroArquivo.'.mp3';
    $NomeArquivoUpload = $_POST["NomeArquivoUpload1"];
    $uri ='gs://mcaires2-teste-arquivosvideo-usa/'.$NomeArquivoServer;

    $arrayTranscricao =array();
    $arrayConfidence =array();
    $transcriçãoStringPura='';

    // fim credenciais e setup

      
     $ValidacaoPasswordResult=ValidarPassword($password);
     $ValidacaoBanResult=IPBAN();


     if ($ValidacaoPasswordResult=="VALIDO" && $ValidacaoBanResult=="VALIDO"){
        
        
        TranscricaoArquivoAudio();
        TranscricaoWord();
        messagetoJson();

     }
    else{
        if ($ValidacaoPasswordResult=="VALIDO") {
            $msg = "IP Recusado, número excessivo de consultas nas últimas 24 horas";
            $arrayMsg=array($msg,0,0);
            echo json_encode($arrayMsg);
        }else{
            $msg = "Password Incorreto";
            $arrayMsg=array($msg,0,0);
            echo json_encode($arrayMsg);
        }

    }

} else{
    $msg= "POST Password Inexistente";
    $arrayMsg=array($msg,0,0);
    echo json_encode($arrayMsg);
}


function messagetoJson(){
    global $arrayTranscricao;
    echo json_encode($arrayTranscricao);

}


function ValidarPassword($password){

   $user_password=$password;
   $pwd_hashed= '$argon2i$v=19$m=65536,t=4,p=1$dkI3YWNDazVVbGJLTXQzMw$Jdcm/6J3ua8uE/oFkZLedCr1yToPQ/zxgW8XnZ71HN0';
      
    if (password_verify($user_password, $pwd_hashed)) {
        $resultado="VALIDO";
    }
    else {
      $resultado="INVALIDO";
    }

    return $resultado;

}

function IPBAN(){

   

    global $servidor, $usuario, $senha, $dbname; //AJAX
    
    $Logindata = new DateTime();
    $Logindata = $Logindata->format('Y-m-d H:i:s');

    $IPUSUARIO ="";
    $IPPROXY ="";

    if (isset($_SERVER['REMOTE_ADDR'])){ $IPUSUARIO = $_SERVER['REMOTE_ADDR']; } else {$IPUSUARIO ="Empty or not Set" ; }
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ $IPPROXY = $_SERVER['HTTP_X_FORWARDED_FOR']; } else {$IPPROXY = $IPUSUARIO; }


    $conn = mysqli_connect($servidor, $usuario, $senha, $dbname);   
    $sql = "SELECT COUNT(*) AS CONTADOR FROM GoogleSpeechToText WHERE `IPUSUARIO` ='$IPUSUARIO' AND   `ACESSODATA` >= (NOW() - INTERVAL 1 DAY);";
    mysqli_set_charset($conn,"utf8");
    $result = mysqli_query($conn,$sql);
    $result2 = mysqli_fetch_array($result);
    $result3 =$result2['CONTADOR'];
    $ban = $result2['CONTADOR'];
    $ban=intval($ban);


    if ($ban>=17){

        mysqli_close($conn);

        $Logindata = new DateTime();
        $Logindata = $Logindata->format('Y-m-d H:i:s');

        
        Enviar_Email_AWS_SES_BAN_TRANSCRICAO($IPUSUARIO,$IPPROXY); // opcional


        $resultadoBan ="EXCESSO";

        return $resultadoBan;

            
    }else{
        
        
        
        $IPUSUARIO ="";
        $IPPROXY ="";
        if (isset($_SERVER['REMOTE_ADDR'])){ $IPUSUARIO = $_SERVER['REMOTE_ADDR']; } else {$IPUSUARIO ="Empty or not Set" ; }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ $IPPROXY = $_SERVER['HTTP_X_FORWARDED_FOR']; } else {$IPPROXY = $IPUSUARIO; }

        

        //Enviar_Email_AWS_SES_BAN_TRANSCRICAO($IPUSUARIO,$IPPROXY);


        $conn = mysqli_connect($servidor, $usuario, $senha, $dbname);   
        $sql = "INSERT INTO `GoogleSpeechToText` (`IPUSUARIO`, `IPPROXY`,`ACESSODATA`) VALUES('$IPUSUARIO','$IPPROXY','$Logindata');";
        
        mysqli_set_charset($conn,"utf8");
        $result = mysqli_query($conn,$sql);
         

        mysqli_close($conn);

        $resultadoBan ="VALIDO";

        return $resultadoBan;

    }


}


function TranscricaoArquivoAudio(){


    global $uri, $arrayTranscricao, $arrayConfidence,$arrayDicionarioPalavras, $transcriçãoStringPura;

    $serviceAccountPath ='../idprojeto/GoogleCredentials.json';


    /** Uncomment and populate these variables in your code */
    // $uri = 'The Cloud Storage object to transcribe (gs://your-bucket-name/your-object-name)';



    // change these variables if necessary
    $encoding = AudioEncoding::ENCODING_UNSPECIFIED;
    $sampleRateHertz = 16000;
    $languageCode = 'pt-BR';
    $model ="command_and_search";
    
    
    // set string as audio content
    $audio = (new RecognitionAudio())
        ->setUri($uri);

    // set config
    $config = (new RecognitionConfig())
        ->setModel($model)
        ->setEncoding($encoding)
        ->setSampleRateHertz($sampleRateHertz)
        ->setLanguageCode($languageCode);
        //->setSpeechContexts($arrayDicionarioPalavras); //changed here
    
    

    // create the speech client
    $client = new SpeechClient(['credentials' => json_decode(file_get_contents($serviceAccountPath), true)]);

    // create the asyncronous recognize operation
    $operation = $client->longRunningRecognize($config, $audio);
    $operation->pollUntilComplete();

    if ($operation->operationSucceeded()) {
        $response = $operation->getResult();

        // each result is for a consecutive portion of the audio. iterate
        // through them to get the transcripts for the entire audio file.
        $contador = 0;
        foreach ($response->getResults() as $result) {
            $alternatives = $result->getAlternatives();
            $mostLikely = $alternatives[0];
            $transcript = $mostLikely->getTranscript();
            $confidence = $mostLikely->getConfidence();

            $transcriçãoString= sprintf('Transcrição - %s : %s' . PHP_EOL, $contador, $transcript);
            $transcriçãoStringConfidence= sprintf('Confiança - %s : %s' . PHP_EOL, $contador, $confidence);
            $transcriçãoStringPura = $transcriçãoStringPura.' '.$transcript;


            array_push($arrayTranscricao,$transcriçãoString);
            array_push($arrayConfidence,$transcriçãoStringConfidence);
            $contador = $contador+1;



        }
    } else {
        print_r($operation->getError());
        $arrayTranscricao=$operation->getError();
    }

    $client->close();



}



function TranscricaoWord() {


    global  $arrayTranscricao, $arrayConfidence, $transcriçãoStringPura;


    $NomeArquivoUpload =$_POST['NomeArquivoUpload1'];
    $extensao = strripos($NomeArquivoUpload,"."); // encontro last dot
    $NomePuroArquivo = substr($NomeArquivoUpload,0,$extensao);


    $diretorio ='../002_TranscricaoWord/';
    $phpWord = new PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    $fontStyleName = 'rStyle';
    $phpWord->addFontStyle($fontStyleName, array('bold' => false, 'italic' => false, 'size' => 13,'name' =>'Times New Roman' ));
        
    $paragraphStyleName = 'pStyle';
    $phpWord->addParagraphStyle($paragraphStyleName, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH ));

    $textrun = $section->addTextRun();


    $textrun->addText('Transcrição Dados Arquivo', ['bold' => true,'size' => 16,'name' =>'Times New Roman','underline' => 'single']);
    $textrun = $section->addTextRun();
    $textrun->addText($NomeArquivoUpload, ['bold' => true,'size' => 16,'name' =>'Times New Roman','underline' => 'single']);
    $textrun = $section->addTextRun();



    $textrun = $section->addTextRun();
    $textrun = $section->addTextRun();
    $textrun = $section->addTextRun();
    $textrun = $section->addTextRun();



    foreach($arrayTranscricao as $piece){

        $section->addText($piece, $fontStyleName,$paragraphStyleName);
        $textrun = $section->addTextRun();
        $textrun = $section->addTextRun();
    

    }


    $textrun = $section->addTextRun();
    $textrun = $section->addTextRun();

   
    $textrun->addText('Relatório de Análise Confiabilidade Transcrição', ['bold' => false,'size' => 14,'name' =>'Times New Roman','underline' => 'single']);
   
    $textrun = $section->addTextRun();

    foreach($arrayConfidence as $piece) {


        $section->addText($piece, $fontStyleName,$paragraphStyleName);
        $textrun = $section->addTextRun();
        


    }


    $textrun = $section->addTextRun();
    $textrun = $section->addTextRun();

   
    $textrun->addText('Relatório Transcrição Texto Corrido', ['bold' => false,'size' => 14,'name' =>'Times New Roman','underline' => 'single']);
   
    $textrun = $section->addTextRun();

    $section->addText($transcriçãoStringPura, $fontStyleName,$paragraphStyleName);


    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($diretorio.$NomePuroArquivo.'.docx');


}







?> 


