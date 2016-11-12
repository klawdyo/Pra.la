<?php
    #
    require 'Pra.la.php';
    
    #Iniciando o objeto. Necessário para todos os exemplos abaixo.
    $prala = new PraLa('http://www.google.com');
    
    #Exemplo Simples - Encurtando a Url
    //echo $prala->shorten();
    
    #Exemplo com configuração do retorno como texto plano
    //echo $prala->asPlain()->shorten();
               
    #Exemplo com configuração do retorno como xml
    //print_r( $prala->asXml()->shorten());
    /*Esse exemplo com XML retornará
    <?xml version="1.0" encoding="UTF-8" ?>
    <prala>
        <original>http://www.THE_URL.com.br/</original> 
        <shortened>http://pra.la/00017</shortened> 
        <short>00017</short>
    </prala>/**/
    
    #Exemplo com customização da URL gerada
    //echo $prala->customize('google')->shorten();     //Retornará http://pra.la/google
    
    #Exemplo com Autenticação
    //echo $prala->auth('apelido', 'chave_secreta')->shorten();
    
    #Gerando uma imagem com um Qrcode a partir da url encurtada.
    //echo '<img src="' . $prala->qrcode() . '" />';
