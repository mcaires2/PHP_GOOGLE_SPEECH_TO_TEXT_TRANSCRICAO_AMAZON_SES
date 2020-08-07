<?php
session_start();

// https://www.cloudways.com/blog/the-basics-of-file-upload-in-php/
// https://pt.stackoverflow.com/questions/374274/como-enviar-o-meu-form-junto-com-outra-vari%C3%A1vel-via-post-do-ajax-para-o-php
// Senha Password do Projeto value='ah5AF_KkY9677qu'

include_once('../db/conexao_marcose.php');
include_once('SES_Email_Amazon_Send.php');
date_default_timezone_set('America/Recife');

ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '256M');
ini_set('max_execution_time', 600);

?>

<?php

$msg ="";

//UploadArquivoAudio();


if(isset($_POST["password"])) {

    $password = $_POST["password"];

    // $mensagem = "PassWordRecebido";
    // echo $mensagem;
    
     $ValidacaoPasswordResult=ValidarPassword($password);
     $ValidacaoBanResult=IPBAN();


     if ($ValidacaoPasswordResult=="VALIDO" && $ValidacaoBanResult=="VALIDO"){
        
        $extensao = strripos($_FILES['Arquivo']['name'],"."); // encontro last dot
        $NomePuroArquivo = substr($_FILES['Arquivo']['name'],0,$extensao);
        $extensao = substr($_FILES['Arquivo']['name'],$extensao); 
        $extensao = strtolower($extensao);
        
        switch ($extensao) {
            
            case ".mp3":
                $mensagem = UploadArquivoAudio($password);
                messagetoJson($mensagem);
            break;

            case ".m4a":
                $mensagem = UploadArquivoAudio($password);
                messagetoJson($mensagem);
            break;
            
            case ".mp4":
                $mensagem = UploadArquivoVideo($password);
                messagetoJson($mensagem);
            break;


            case ".webm":
                $mensagem = UploadArquivoVideo($password);
                messagetoJson($mensagem);
            break;

            case ".mov":
                $mensagem = UploadArquivoVideo($password);
                messagetoJson($mensagem);
            break;
           
            default:
                $mensagem = "A extensão do arquivo é inválida, procedimento cancelado: ".$extensao;
                $arrayMsg=array($mensagem,0,0);
                messagetoJson($arrayMsg);

                
            break;
        }

       
     } else{
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







function messagetoJson($mensagem){

    echo json_encode($mensagem, JSON_UNESCAPED_UNICODE);

}


function ValidarPassword($password){

    $user_password=$password;
    $pwd_hashed= '$argon2i$v=19$m=65536,t=4,p=1$dkI3YWNDazVVbGJLTXQzMw$Jdcm/6J3ua8uE/oFkZLedCr1yToPQ/zxgW8XnZ71HN0'; // VOCE VAI PRECISAR GERAR UM PASSWORD E SUBMETER ELE PELA FERRAMENTA DO ARGON2I PARA EXTRAIR O RESULTADO DA CRIPTOGRAFIA PARA POR AQUI
    //Senha Password do Projeto value='ah5AF_KkY9677qu' ESTA É A SENHA QUE AUTENTICA COM A CRIPTOGRAFIA ACIMA
       
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
 
        
         Enviar_Email_AWS_SES_BAN_UPLOAD($IPUSUARIO,$IPPROXY); // OPTCIONAL, VEJA NO README
        

         $resultadoBan ="Upload Cancelado, excesso de tentativas nas últimas 24 horas";
 
         return $resultadoBan;
 
             
     }else{
         
               
         
         $IPUSUARIO ="";
         $IPPROXY ="";
         if (isset($_SERVER['REMOTE_ADDR'])){ $IPUSUARIO = $_SERVER['REMOTE_ADDR']; } else {$IPUSUARIO ="Empty or not Set" ; }
         if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ $IPPROXY = $_SERVER['HTTP_X_FORWARDED_FOR']; } else {$IPPROXY = $IPUSUARIO; }

         //Enviar_Email_AWS_SES_BAN_UPLOAD($IPUSUARIO,$IPPROXY);
 
 
         $conn = mysqli_connect($servidor, $usuario, $senha, $dbname);   
         $sql = "INSERT INTO `GoogleSpeechToText` (`IPUSUARIO`, `IPPROXY`,`ACESSODATA`) VALUES('$IPUSUARIO','$IPPROXY','$Logindata');";
         
         mysqli_set_charset($conn,"utf8");
         $result = mysqli_query($conn,$sql);
          
 
         mysqli_close($conn);
 
         $resultadoBan ="VALIDO";
 
         return $resultadoBan;
 
     }
 
 
}
 

function UploadArquivoAudio($password){
 
 
    $msg = "";
    $novo_nome ="";
    $diretorio ="";
    $novo_nome ="";
    $t=time();
    
    if(isset($_FILES['Arquivo']['name'])){
        
        $extensao = strripos($_FILES['Arquivo']['name'],"."); // encontro last dot
        $NomePuroArquivo = substr($_FILES['Arquivo']['name'],0,$extensao);
        $extensao = substr($_FILES['Arquivo']['name'],$extensao); 
        $extensao =strtolower($extensao);
    
        $NomeArquivoUsuario = $_FILES['Arquivo']['name'];
        $novo_nome = $t.$extensao;
                    
                
    
                            
        $diretorio ='../000_Audio/';

        move_uploaded_file($_FILES['Arquivo']['tmp_name'], $diretorio.$novo_nome);
        
                                    
        $msg = 'Upload Arquivo - '.$NomePuroArquivo.$extensao. ' - realizado com sucesso, prossiga com a transcrição...';
        $arrayMsg=array($msg,$novo_nome,1,$NomeArquivoUsuario,$password);

        return $arrayMsg;
    
    
            
    
            
    } else {


        $msg = "Erro no Upload do Arquivo, reveja sua conexão e tente novamente";
        $arrayMsg=array($msg,0,0);
        return $arrayMsg;


    }
    
}


function UploadArquivoVideo($password){


    $msg = "";
    $novo_nome ="";
    $diretorio ="";
    $novo_nome ="";
    $t=time();
    
    if(isset($_FILES['Arquivo']['name'])){
    
        $extensao = strripos($_FILES['Arquivo']['name'],"."); // encontro last dot
        $NomePuroArquivo = substr($_FILES['Arquivo']['name'],0,$extensao);
        $extensao = substr($_FILES['Arquivo']['name'],$extensao); 
        $extensao =strtolower($extensao);

        $NomeArquivoUsuario = $_FILES['Arquivo']['name'];
        $novo_nome = $t.$extensao;

        $diretorio ='../001_Video/';

        move_uploaded_file($_FILES['Arquivo']['tmp_name'], $diretorio.$novo_nome);
        
                                    
        $msg = 'Upload Arquivo - '.$NomePuroArquivo.$extensao. ' - realizado com sucesso, prossiga com a transcrição...';
        $arrayMsg=array($msg,$novo_nome,1,$NomeArquivoUsuario,$password);

        return $arrayMsg;

    } else {


        $msg = "Erro no Upload do Arquivo, reveja sua conexão e tente novamente";
        $arrayMsg=array($msg,0,0);
        return $arrayMsg;


    }



}






















?>
    
    
 
 

