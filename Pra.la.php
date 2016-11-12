<?php
/**
  *---------------------------------
  *     INTRODUÇÃO
  *---------------------------------
  * Provê funcionalidades de geração de URLs encurtadas com o serviço PRA.LA
  *
  * @author Cláudio Medeiros <contato@claudiomedeiros.net>
  *
  *---------------------------------
  *     COMO USAR
  *---------------------------------
  * //Iniciando o objeto. Necessário para todos os exemplos abaixo.
  * $prala = new PraLa('http://google.com');
  *
  * //Exemplo Simples - Encurtando a Url
  * echo $prala->shorten();
  *
  * //Exemplo com configuração do retorno como xml
  * echo $prala->asXml()
  *            ->shorten();
  *            
  * //Esse exemplo com XML retornará
  * <?xml version="1.0" encoding="UTF-8" ?>
  * <prala>
  *     <original>http://www.THE_URL.com.br/</original> 
  *     <shortened>http://pra.la/00017</shortened> 
  *     <short>00017</short>
  * </prala>
  * 
  * //Exemplo com customização da URL gerada
  * echo $prala->customize('iPad2011')
  *            ->shorten();     //Retornará http://pra.la/iPad2011
  * 
  * //Exemplo com Autenticação
  * echo $prala->auth('apelido', 'chave_secreta')
  *            ->shorten();
  *
  * //Gerando uma imagem com um Qrcode a partir da url encurtada.
  * echo '<img src="' . $prala->qrcode() . '" />';
  *
  *---------------------------------
  *     CHANGELOG
  *---------------------------------
  * 1.0 19/11/2010 Commit inicial
  *     [+] customize() permite a personalização da url gerada
  *     [+] asPlain() define "texto plano" como formato de retorno padrão
  *     [+] asXml() define "xml" como formato de retorno padrão
  *     [+] auth() permite configurar autenticação e posterior acompanhamento das estatísticas geradas
  *     [+] shorten() explicitamente encurta a URL
  *     [+] qrcode() gera uma imagem contendo um Qrcode da url encurtada
  *---------------------------------
  *     PRÓXIMAS VERSÕES
  *---------------------------------
  *     - Permitir enviar várias urls para o construtor da classe
  *
  *     - customize() precisa tratar o input e remover caracteres não desejáveis
  *
  *     
  */
class PraLa{
    
    /**
      * @var string $url
      * Armazena a URL informada no construtor da classe
      */
    protected $url;
    
    /**
      * @var string $shortUrl
      * Armazena a url já encurtada
      */
    protected $shortUrl;
    
    /**
      * @var string $custom
      * Armazena os dados de customização
      */
    protected $custom;

    /**
      * @var array $authData
      * Armazena os dados de autenticação.
      */
    protected $authData;
    
    /**
      * @var array $format
      * Armazena o tipo de retorno desejado
      */
    protected $format = 'plain';
    
    
    /**
      * Construtor da Classe
      *
      * @param string $url
      * @return
      */
    public function __construct($url = null){
        if(empty($url)){
            throw new Exception('O parâmetro $url é obrigatório');
        }
        else{
            $this->url = $url;
        }
    }
    
    /************************************
     *
     *      CONFIGURATION METHODS
     *
     ************************************/
  

    /**
      * Define a URL customizada
      *
      * @param string $custom Parte personalisável da URL
      * @return object
      */
    public function customize($custom = null){
        if(empty($custom)){
            $this->custom = null;
        }
        else{
            $this->custom = $custom;
        }
        
        return $this;
    }
    
    
    /**
      * Configura autenticação para as requisições, possibilitando o acompanhamento das
      * estatísticas geradas
      *
      * @param string $username Nome de usuário
      * @param string $secret Chave secreta de acesso
      * @return object
      */
    public function auth($username = null, $secret = null){
        if(empty($username) || empty($secret)){
            throw new InvalidArgumentException('$username e $secret são parâmetros obrigatórios');
        }
        else{
            $this->authData = array(
                'user' => $username,
                'key' => $secret
            );
        }
        
        return $this;
    }

    /**
      * Configura o retorno para ser exibido como texto plano
      *
      * @return object
      */
    public function asPlain(){
        $this->format = 'plain';
        
        return $this;
    }

    /**
      * Configura o retorno para ser exibido como Xml
      *
      * @return object
      */
    public function asXml(){
        $this->format = 'xml';
        
        return $this;
    }
    
    
    /************************************
     *
     *      RETURNING METHODS
     *
     ************************************/
    
    
    /**
      * Encurta a URL explicitamente
      *
      * @return Retorna a url encurtada, ou FALSE em caso de erro
      */
    public function shorten(){
        $params = array('url' => $this->url);
        
        //Tem uma url personalizada
        if(!empty($this->custom)) $params['custom'] = $this->custom;
        
        //Definindo o formato
        $params['format'] = $this->format;
        
        
        //Verificando os dados de autenticação
        if(!empty($this->authData)) {
            $params['key'] =  $this->authData['key'];
            $params['user'] = $this->authData['user'];
        }
        
        //Requisitando a página de resultados
        $results = $this->request('http://pra.la/api', $params);
        
        //Se tratar-se de um XML, retire os dados necessários
        if($this->format == 'xml'){
            $xml = new SimpleXMLElement($results);
            $this->shortUrl = (string) reset($xml->xpath('/prala/shortened'));
        }
        else{
            $this->shortUrl = $results;
        }
        
        //Finalizando com o retorno dos resultados.
        return $results;        
    }
    
    /**
      * Retorna a url de uma imagem Qrcode, ou FALSE em caso de erro.
      * qrcode() verificará se shorten() foi chamado anteriormente, e o
      * chama se for necessário.
      * 
      * @param  string $width As dimensões da imagem. A imagem é quadrada.
      * @return string Url da imagem gerada
      */
    public function qrcode($width = 170){
        if(empty($this->shortUrl)){
            $this->shorten();
        }
        
        return "http://chart.apis.google.com/chart?chf=a,s,000000&chs=" .
                $width . "x" . $width . "&cht=qr&chl=" . $this->shortUrl;
    }
    
    /************************************
     *
     *      INTERNAL METHODS
     *
     ************************************/
    
    /**
      * Envia a requisição GET
      *
      * @param  
      * @return 
      */
    protected function request($url, $params = null){
        if(!empty($params)){
            if(is_array($params)){
                $params = '?' . http_build_query($params);
            }
            else{
                $params = '?' . $params;
            }
        }
        //print_r($url.$params);echo "\n";
        return file_get_contents($url . $params);        
    }
}