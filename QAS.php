<?php

/**
 * Created by PhpStorm.
 * User: Mariusz Soltys
 * Date: 01/09/2015
 * Time: 09:19
 */
class QAS
{
    /**
     * @var string default WSDL file location
     */
    public $wsdl = "http://vsvr-app539.euser.eroot.eadidom.com:2026/proweb.wsdl";

    /**
     * @var array Default QAS request configuration
     */
    private $defaults = [
        'Engine' => [
            '_' => 'Singleline',
            'Flatten' => false,
            'Intensity' => "Exact",
            'Threshold' => '5',
            'Timeout'=>'30',
        ],
        'Country' => 'GBR',
        'Layout' => '< Default >',
        'Refinement' => '',
        'Search' => 'Suffolk'
    ];

    /**
     * @var SoapClient
     */
    public $client;

    /**
     * @param string $wsdl WSDL file location
     */
    function __construct($wsdl = null) {
        if ( empty($wsdl) )
            $wsdl = $this->wsdl;

        $this->client = new SoapClient($wsdl);
    }

    /**
     * @param array|object $json JSON object containing QAS request configuration and action, see $defaults
     * @return string JSON encoded object
     */
    public function call ($json) {

        $action = $json['action'];

        if(empty($action))
            return "No 'action' parameter specified";
        try {
            $params = array_merge( $this->defaults, $json['params'] );
            $data = $this->client->$action( $params );

            return $this->response( $data );

        }catch (SoapFault $e){

            return $e;

        }
    }

    /**
     * @param string $query Search phrase
     * @return string JSON encoded object
     */
    public function search ($query) {
        $results = $this->call([
            'action'=>'DoSearch',
            'params'=>[
                'Search'=>$query
            ]]);

        $results = json_decode($results);

        if(is_object($results->QAPicklist->PicklistEntry) && $results->QAPicklist->PicklistEntry->CanStep){
            return $this->refine($results->QAPicklist->PicklistEntry->Moniker);
        }

        return $results;
    }

    /**
     * @param string $moniker
     * @param string $query Search refinement
     * @return string JSON
     */
    public function refine ($moniker, $query='') {
        $results = $this->call([
            'action'=>'DoRefine',
            'params'=>[
                'Refinement' => $query,
                'Moniker' => $moniker
            ]]);
        return $results;
    }

    /**
     * @return string JSON encoded array containing Available QAS methods
     */
    public function getMethods() {
        $functions = $this->client->__getFunctions();
        return $this->response( $functions );
    }

    /**
     * @param object $data Data object to JSON encode
     * @return string
     */
    private function response( $data ) {
        return json_encode($data);
    }
}