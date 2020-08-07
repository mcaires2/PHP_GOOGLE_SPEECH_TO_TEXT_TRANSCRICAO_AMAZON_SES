<?php
include_once('../db/conexao_marcose.php');
include_once('SES_Email_Amazon_Send.php');
date_default_timezone_set('America/Recife');

ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '256M');
ini_set('max_execution_time', 600);

require_once "../vendor/autoload.php";
include '../vendor/autoload.php';


// Imports the Cloud Storage client library.
use Google\Cloud\Storage\StorageClient;






$arrayExtensaoDeletar = array('.mp3','.m4a','.mp4', '.webm','.mov'); // definir tipos e arquivos que serão apagados do servidor depois de passados para o bucket...



// preciso checar se o arquivo já é audio ou video e se for video preciso converter para audio .mp3

$NomeArquivoUploadServer =$_POST['feedbackNomeArquivoServer'];

$extensao = strripos($NomeArquivoUploadServer,"."); // encontro last dot
$NomePuroArquivo = substr($NomeArquivoUploadServer,0,$extensao);
$extensao = substr($NomeArquivoUploadServer,$extensao); 
$extensao =strtolower($extensao);

        switch ($extensao) {
            
            
            
            case ".mp3":
                
            break;
            
            
            case ".m4a":
                ConverterArquivoAudioToAudio();
            break;



            case ".mp4":
                ConverterArquivoVideoAudio();
            break;


            case ".webm":
                ConverterArquivoVideoAudio();
            break;

            case ".mov":
                ConverterArquivoVideoAudio();
            break;


            default:
            $mensagem = "A extensão do arquivo é inválida ou o arquivo não foi encontrado para transferência ao bucket, procedimento cancelado: ".$extensao;
            $arrayMsg=array($mensagem,0,0);
            messagetoJson2($arrayMsg);
                  
           
                
        }




//////////////////////////Google Storage Part//////////////////////////

// credentials

$projectId ='AQUI VOCÉ VAI TER DE DIGITAR A ID DO SEU PROJETO NA GOOGLE CLOUD ONDE ESTA ARMAZENADO O BUCKET CRIADO';
$serviceAccountPath ='../idprojeto/GoogleCredentials.json';

$config = [
    'keyFilePath' => $serviceAccountPath,
    'projectId' => $projectId,
];


// File, Bucket and Path 

$bucketName = 'NOME DO BUCKET ONDE O ARQUIVO SERA ARMAZENADO - TEM DE SER UM QUE EXISTA NO PROJETO ACIMA MENCIONADO';
$objectName =$NomePuroArquivo.'.mp3';
$source ='../000_Audio/'.$objectName;


//  upload to bucket

$ControleResultadoUploadBucket =0;
$mensagem = upload_object($bucketName, $objectName, $source);
$arrayMsg=array($mensagem,1);

DeletarArquivosServidorLocal($ControleResultadoUploadBucket); // arquivo já está no bucket, deleto do server para economizar espaço e proteger informação com o firewall do cloud..
DeletarArquivosServidorLocalVideo($ControleResultadoUploadBucket);
messagetoJson($arrayMsg,JSON_UNESCAPED_UNICODE);



function messagetoJson2($mensagem){

    echo json_encode($mensagem, JSON_UNESCAPED_UNICODE);
    exit(); // sai do script

}


function upload_object($bucketName, $objectName, $source){
    
    global $config, $ControleResultadoUploadBucket;
    
    $storage = new StorageClient($config);
    $file = fopen($source, 'r');
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->upload($file, [
        'name' => $objectName
    ]);
    $mensagem = sprintf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source), $bucketName, $objectName);
    $ControleResultadoUploadBucket =1;
    return $mensagem; // sprintf store the formatted string to the variable; printf output it but you cannot store to variable...
}



function messagetoJson($arrayMsg){

    echo json_encode($arrayMsg);

}

function DeletarArquivosServidorLocal($ControleResultadoUploadBucket){

    global $arrayExtensaoDeletar;


    if ($ControleResultadoUploadBucket ==1){
    
        $dir3 ='../000_Audio/';
        

        foreach (scandir($dir3) as $file) {
 
            if ($file !== '.' and $file !== '..') {
               
               $extensao = strripos($file,"."); // encontro last dot
               $extensao = substr($file,$extensao);  // extraio a extensao do arquivo
               $extensao =strtolower($extensao); // coloco a extensão extraida para lowercase
               
               if (in_array($extensao, $arrayExtensaoDeletar)){
      
                unlink($dir3.$file);

                }



            }

        }
    }
}


function DeletarArquivosServidorLocalVideo($ControleResultadoUploadBucket){

    global $arrayExtensaoDeletar;
    
    if ($ControleResultadoUploadBucket ==1){
    
        $dir3 ='../001_Video/';
        
        

        foreach (scandir($dir3) as $file) {
 
            if ($file !== '.' and $file !== '..') {
               
               $extensao = strripos($file,"."); // encontro last dot
               $extensao = substr($file,$extensao);  // extraio a extensao do arquivo
               $extensao =strtolower($extensao); // coloco a extensão extraida para lowercase
               
               if (in_array($extensao, $arrayExtensaoDeletar)){
      
                  unlink($dir3.$file);
                }



            }

        }
    }
}



function ConverterArquivoVideoAudio(){
    global $NomeArquivoUploadServer;
    
    $NomeServidorScript =strtoupper($_SERVER['SERVER_NAME']);
    

    $extensao = strripos($NomeArquivoUploadServer,"."); // encontro last dot
    $NomePuroArquivo = substr($NomeArquivoUploadServer,0,$extensao);
    $extensao = substr($NomeArquivoUploadServer,$extensao); 

    if ($NomeServidorScript=="GOOGLESPEECHTOTEXT") {   // ESTA PARTE É INTERESSANTE PORQUE A DEPENDER SE ESTÁ NA MAQUINA WINDOWS OU LINUX MUDA A FORMA DE LOCALIZAR OS ARQUIVOS FFMPEG, FIZ UMA FORMA DELE IDENTIFICAR MEU SERVIDOR DO WINDOWS E CASO NEGATIVO PROCURA OS ARQUIVOS DA FORMA DO UBUNTU

        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => 'C:\ffmpeg\bin\ffmpeg.exe', // MUDAR O LOCAL DO ARQUIVO NA SUA MAQUINA DO WINDOS, FULL PATH
            'ffprobe.binaries' => 'C:\ffmpeg\bin\ffprobe.exe'  // MUDAR O LOCAL DO ARQUIVO NA SUA MAQUINA DO WINDOS, FULL PATH
        ]);
        

    } else{ // UBUNTU
        
        $ffmpeg = \FFMpeg\FFMpeg::create();
        // means that I am not on Windows enviroment but sure on Ubuntu machine that takes the binaries by itself after installation 
        // sudo apt install ffmpeg
       
        
    }

        // Open your video file
        $video = $ffmpeg->open( '../001_Video/'.$NomeArquivoUploadServer);
        
        // Set an audio format
        $audio_format = new FFMpeg\Format\Audio\Mp3();
        
        // Extract the audio into a new file as mp3
        $video->save($audio_format, '../000_Audio/'.$NomePuroArquivo.'.mp3');




}

function ConverterArquivoAudioToAudio(){


    global $NomeArquivoUploadServer;

    $NomeServidorScript =strtoupper($_SERVER['SERVER_NAME']);
    $extensao = strripos($NomeArquivoUploadServer,"."); // encontro last dot
    $NomePuroArquivo = substr($NomeArquivoUploadServer,0,$extensao);
    $extensao = substr($NomeArquivoUploadServer,$extensao); 

        if ($NomeServidorScript=="GOOGLESPEECHTOTEXT") { // MESMA SITUAÇÃO ACIMA EXPLICADA...

            $ffmpeg = \FFMpeg\FFMpeg::create([
                'ffmpeg.binaries'  => 'C:\ffmpeg\bin\ffmpeg.exe',  // MUDAR O LOCAL DO ARQUIVO NA SUA MAQUINA DO WINDOS, FULL PATH
                'ffprobe.binaries' => 'C:\ffmpeg\bin\ffprobe.exe'  // MUDAR O LOCAL DO ARQUIVO NA SUA MAQUINA DO WINDOS, FULL PATH
            ]);
            
    
        } else{    
    
            $ffmpeg = FFMpeg\FFMpeg::create();
        }



    $audio = $ffmpeg->open( '../000_Audio/'.$NomeArquivoUploadServer);
    $audio_format = new FFMpeg\Format\Audio\Mp3();
    $audio->save($audio_format, '../000_Audio/'.$NomePuroArquivo.'.mp3');


}



?>