<?php

/**
 * Created by PhpStorm.
 * User: Mariusz Soltys
 * Date: 01/09/2015
 * Time: 09:19
 */
class QAS
{
    public $wsdl = "http://vsvr-app539.euser.eroot.eadidom.com:2026/proweb.wsdl";

    private $defaults = [
        'Engine' => [
            '_' => 'Singleline',
            'Flatten' => false,
            'Intensity' => "Exact",
            'Threshold' => '5',
            'Timeout'=>'30',
        ],
        'Country' => 'GBR',
        'Search' => 'Suffolk',
        'Refinement' => ''
    ];

    /**
     * @var SoapClient
     */
    public $client;

    function __construct($wsdl = null) {
        if ( empty($wsdl) )
            $wsdl = $this->wsdl;
        $this->client = new SoapClient($wsdl);
    }

    public function call ($json) {

        $action = $json['action'];

        if(empty($action))
            return "No 'action' parameter specified";
        try {
            $params = array_merge( $this->defaults, $json['params'] );
            $data = $this->client->$action( $params );

            return $this->response( $data );

        }catch (SoapFault $e){

            return $e->getMessage();

        }
    }

    public function search ($query) {
        return $this->call([
            'action'=>'DoSearch',
            'params'=>[
                'Search'=>$query
            ]]);
    }

    public function getMethods() {
        $functions = $this->client->__getFunctions();
        return $this->response( $functions );
    }

    private function response( $data ) {
        return json_encode($data);
    }
}

$qas = new QAS();

print $qas->call([
    'action'=>'DoSearch',
    'params'=>[
        'Search'=>'IP12LL'
    ]
]);