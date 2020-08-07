<?php
//HOME is required for AWS store credentials to work.
//putenv('HOME=/root');
// SE PREFERIR MODIFICAR O DIRETORIO HOME USAR DICAS ACIMA, DO CONTRÁRIO PONHA O ARQUIVO CREDENTIALS NO DIRETORIO RAIZ DE ONDE VAI RODAR ESTE PROJETO POR EXEMPLO:
//C:\.AWS\credentials ou E:\.AWS\credentials ou no Ubuntu /.aws/credentials - lembrar que vc tem de ter acesso de leitura para este arquivo credentials, se necessário, configure chmod 


// https://docs.aws.amazon.com/ses/latest/DeveloperGuide/create-shared-credentials-file.html
// https://docs.aws.amazon.com/ses/latest/DeveloperGuide/examples-send-using-sdk.html
// https://docs.aws.amazon.com/ses/latest/DeveloperGuide/examples-send-using-smtp.html - caso o objetivo seja usar a via da autenticação smtp - tem e habilitar phpmailer...
// credenciais em arquivo próprio na raiz do drive atual /.aws/credentials (sem extensão, veja no gmail por mais explicações)



include_once('../db/conexao_marcose.php');
date_default_timezone_set('America/Recife');

require '../vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;


function Enviar_Email_AWS_SES_BAN_UPLOAD($IPUSUARIO,$IPPROXY){

    $Logindata = new DateTime();
    $Logindata = $Logindata->format('d-m-Y H:i:s');


    $SesClient = new SesClient([
        'profile' => 'default',
        'version' => '2010-12-01',
        'region'  => 'us-east-1'
                
    ]);

        

                                // Replace sender@example.com with your "From" address.
                                // This address must be verified with Amazon SES.
    $sender_email = 'sender@example.com';

                                // Replace these sample addresses with the addresses of your recipients. If
                                // your account is still in the sandbox, these addresses must be verified.
    $recipient_emails = ['TO@gmail.com']; // list sort of

                                // Specify a configuration set. If you do not want to use a configuration
                                // set, comment the following variable, and the
                                // 'ConfigurationSetName' => $configuration_set argument below.
                                //$configuration_set = 'ConfigSet';

    $subject = 'IP Bloqueado por 24 horas no Projeto Speech to Text -'.$Logindata;
    $plaintext_body = 'Sucessivas tentativas de acesso Speech to Text...'.
                      'IP Usuário : '.$IPUSUARIO. ', IP Proxy : '.$IPPROXY.' Data: '.$Logindata;
    
    $html_body ='<h1>Speech to Text - BAN - Upload </h1>'.
                 '<div> Sucessivas tentativas de acesso Speech to Text...'.
                 'IP Usuário : '.$IPUSUARIO. ', IP Proxy : '.$IPPROXY.' Data: '.$Logindata.
                 '</div>'.
                '<p>Email enviado automaticamente usando <a href="https://aws.amazon.com/ses/">'.
                'Amazon SES</a> e <a href="https://aws.amazon.com/sdk-for-php/">'.
                'AWS SDK for PHP</a>.</p>';
    $char_set = 'UTF-8';

    try {
        $result = $SesClient->sendEmail([
            'Destination' => [
                'ToAddresses' => $recipient_emails,
            ],
            'ReplyToAddresses' => [$sender_email],
            'Source' => $sender_email,
            'Message' => [
            'Body' => [
                'Html' => [
                    'Charset' => $char_set,
                    'Data' => $html_body,
                ],
                'Text' => [
                    'Charset' => $char_set,
                    'Data' => $plaintext_body,
                ],
            ],
            'Subject' => [
                'Charset' => $char_set,
                'Data' => $subject,
            ],
            ],
                                                // If you aren't using a configuration set, comment or delete the
                                                // following line
                                                //'ConfigurationSetName' => $configuration_set,
        ]);
        $messageId = $result['MessageId'];
        //echo("Email sent! Message ID: $messageId"."\n");
    } catch (AwsException $e) {
        // output error message if fails
        //echo $e->getMessage();
        //echo("The email was not sent. Error message: ".$e->getAwsErrorMessage()."\n");
       // echo "\n";
    }

}


function Enviar_Email_AWS_SES_BAN_TRANSCRICAO($IPUSUARIO,$IPPROXY){

    $Logindata = new DateTime();
    $Logindata = $Logindata->format('d-m-Y H:i:s');


    $SesClient = new SesClient([
        'profile' => 'default',
        'version' => '2010-12-01',
        'region'  => 'us-east-1'
                
    ]);

        

                                // Replace sender@example.com with your "From" address.
                                // This address must be verified with Amazon SES.
    $sender_email = 'sender@example.com';

                                // Replace these sample addresses with the addresses of your recipients. If
                                // your account is still in the sandbox, these addresses must be verified.
    $recipient_emails = ['TO@gmail.com']; // list sort of

                                // Specify a configuration set. If you do not want to use a configuration
                                // set, comment the following variable, and the
                                // 'ConfigurationSetName' => $configuration_set argument below.
                                //$configuration_set = 'ConfigSet';

    $subject = 'IP Bloqueado por 24 horas no Projeto Speech to Text -'.$Logindata;
    $plaintext_body = 'Sucessivas tentativas de acesso Speech to Text...'.
                      'IP Usuário : '.$IPUSUARIO. ', IP Proxy : '.$IPPROXY.' Data: '.$Logindata;
    
    $html_body ='<h1>Speech to Text - BAN - Transcrição </h1>'.
                 '<div> Sucessivas tentativas de acesso Speech to Text...'.
                 'IP Usuário : '.$IPUSUARIO. ', IP Proxy : '.$IPPROXY.' Data: '.$Logindata.
                 '</div>'.
                '<p>Email enviado automaticamente usando <a href="https://aws.amazon.com/ses/">'.
                'Amazon SES</a> e <a href="https://aws.amazon.com/sdk-for-php/">'.
                'AWS SDK for PHP</a>.</p>';
    $char_set = 'UTF-8';

    try {
        $result = $SesClient->sendEmail([
            'Destination' => [
                'ToAddresses' => $recipient_emails,
            ],
            'ReplyToAddresses' => [$sender_email],
            'Source' => $sender_email,
            'Message' => [
            'Body' => [
                'Html' => [
                    'Charset' => $char_set,
                    'Data' => $html_body,
                ],
                'Text' => [
                    'Charset' => $char_set,
                    'Data' => $plaintext_body,
                ],
            ],
            'Subject' => [
                'Charset' => $char_set,
                'Data' => $subject,
            ],
            ],
                                                // If you aren't using a configuration set, comment or delete the
                                                // following line
                                                //'ConfigurationSetName' => $configuration_set,
        ]);
        $messageId = $result['MessageId'];
        //echo("Email sent! Message ID: $messageId"."\n");
    } catch (AwsException $e) {
        // output error message if fails
        //echo $e->getMessage();
        //echo("The email was not sent. Error message: ".$e->getAwsErrorMessage()."\n");
       // echo "\n";
    }

}
?>