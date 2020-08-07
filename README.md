# PHP_GOOGLE_SPEECH_TO_TEXT_TRANSCRICAO_AMAZON_SES
PHP - Upload e Transcrição Audio e Video usando Google Speech to Text e Email SES Amazon
Este projeto objetiva receber um arquivo de áudio ou vídeo via upload em um navegador comum, converter, se necessário, para um arquivo de áudio formato mp3 e submeter para transcrição em língua portuguesa usando o serviço Speech to Text do Google Cloud.
Terminada a transcrição –   assíncrona - o projeto recebe os dados e realiza duas condutas. 
Na primeira delas, apresenta a transcrição na tela do navegador separada conforme retorno da ferramenta de transcrição. 
Na segunda, armazena a transcrição em um arquivo Word que fica à disposição do usuário para se fazer download.  Neste arquivo do Word a transcrição é retornada com as separações que a ferramenta do Google sugeriu, apresentando, em seguida, relatório técnico de confiabilidade da melhor hipótese de transcrição (também por trecho) e por último, relatório contendo a transcrição contínua do feedback recebido pelo Google Speech to Text.

 

Para que o projeto funcione sem erros é necessário instalar os seguintes pacotes no ambiente de desenvolvimento e produção.

{
    "require": {
        "phpoffice/phpword": "^0.17.0",
        "smalot/pdfparser": "^0.14.0",
        "google/cloud-speech": "^1.2",
        "google/cloud-storage": "^1.22",
        "php-ffmpeg/php-ffmpeg": "^0.16.0",
        "google/protobuf": "^3.12",
        "ext-bcmath": "*",
        "aws/aws-sdk-php": "^3.147"
 
    }
}

Usei a metodologia de instalação via composer, embora, no projeto que segue neste git, conste todos os arquivos necessários para rodar a proposta aqui apresentada.

Você também vai precisar de gerar um arquivo JSON no Google Cloud que o habilite a usar a API Speech to Text e o Google Storage que deve ser armazenada no subdiretório idprojeto.

No diretório raiz do projeto devem estar os seguintes arquivos:

 

Outro ponto relevante é a criação de um banco de dados para armazenar IP de utilização da ferramenta, data e horário ao fim de possibilitar controle de utilização quantitativa nas últimas 24 horas. 

Este controle está definido em 17 utilizações e é conduzido em dois momentos: no upload do arquivo que se pretende transcrever e na chamada da API de transcrição. 

O nome do banco de dados utilizado é GoogleSpeechToText e a estrutura nele criada é a seguinte:

 


Usamos - opcional – a ferramenta SES da Amazon para mandar um email  com dados do  IP do usuário se o limite de utilizações das últimas 24 horas fosse atingido (dupla checagem upload e na transcrição).

Se decidir manter esta parte do projeto tem de atentar aos seguintes passos: a) cadastrar uma conta ativa na Amazon SES; b) armazenar o arquivo credentials a partir do drive raiz que está a rodar este projeto. 
Seguem dois exemplos: 
Windows: C:\.aws\credentials (o arquivo não pode ter extensão) ou
Ubuntu: /.aws/credentials  (o arquivo não pode ter extensão)

Caso prefira desabilitar esta parte do projeto, vá para o subdiretório ajax, abra os arquivos .php e desabilite o call das funções 
•	Enviar_Email_AWS_SES_BAN_TRANSCRICAO($IPUSUARIO,$IPPROXY)
•	Enviar_Email_AWS_SES_BAN_UPLOAD($IPUSUARIO,$IPPROXY)


A estrutura de diretórios deste projeto segue a seguinte proposição presente no código criado:

 

Detalhes dessa estrutura que se recomenda replicar até se dominar a codificação do projeto e modificar ao jeito do desenvolvedor:

 

O projeto foi codificado para utilização com PHP 7.2, JavaScritp e Jquery.
Em alguns momentos usa ajax para solicitações e recebimento de dados assíncronos.
Retiramos, do código, todas as senhas e dados identificadores para interação com os serviços SES da Amazon e do Google Storage (para transcrição de arquivos com mais de 1 minuto de áudio é necessário fazer o upload do arquivo para um bucket do Google Storage).

Segue abaixo link demonstrativo do projeto de transcrição funcionando:

https://drive.google.com/file/d/1Z97axnbK0Z90vxRrPMxSub7I2awhAZQF/view?usp=sharing

Marcos Luz
