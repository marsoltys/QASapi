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
    public $wsdl = "http://vsvr-dev897.euser.eroot.eadidom.com:2026/proweb.wsdl";

    /**
     * @var array Default QAS request configuration
     */
    private $defaults = [
        'Engine' => [
            '_' => 'Singleline',
            'Flatten' => false,
            'Intensity' => "Close",
            'Threshold' => '5',
            'Timeout'=>'30',
        ],
        'Country' => 'APR',
        'Layout' => 'PAFWEB',
        'Refinement' => '',
        'Search' => 'Suffolk'
    ];

    private $response = null;

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
     * @return Object|SoapFault response from QAS server
     */
    public function call ($json) {

        $action = $json['action'];

        if(empty($action))
            return "No 'action' parameter specified";
        try {
            $params = array_merge((array)$this->defaults, (array)$json['params'] );
            $this->response = $this->client->$action( $params );

            return $this->response ;

        }catch (SoapFault $e){

            return $e;
        }
    }

    /**
     * @param string $query Search phrase
     * @return Object|SoapFault response from QAS server
     */
    public function search ($query) {
        $results = $this->call([
            'action'=>'DoSearch',
            'params'=>[
                'Search'=>$query
            ]]);

        if(is_object($results->QAPicklist->PicklistEntry) && $results->QAPicklist->PicklistEntry->CanStep){
            return $this->refine($results->QAPicklist->PicklistEntry->Moniker);
        }

        return $results;
    }

    /**
     * @param string $moniker
     * @param string $query Search refinement
     * @return Object|SoapFault response from QAS server
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

    public function getAddressDetails($moniker, $options=array()) {
        $params = ['Moniker' => $moniker];
        $options = array_merge($options, $params);
        $results = $this->call([
            'action'=>'DoGetAddress',
            'params'=>$options
        ]);

        return $results;
    }

    /**
     * @return string JSON encoded array containing Available QAS methods
     */
    public function getMethods() {
        $functions = $this->client->__getFunctions();
        return $functions ;
    }

    /**
     * @param object $data Data object to JSON encode
     * @return string
     */
    public function getJson() {
        if(!empty($this->response))
            return json_encode($this->response);
        else
            return "{'Please make a request first'}";
    }
}