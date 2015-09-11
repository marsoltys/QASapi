<?php

/**
 *
 * Project: Qas Soap Client
 * Date: 01/09/2015
 * @author Mariusz Soltys.
 * @version 1.0.0
 * @license http://opensource.org/licenses/MIT
 *
 */

namespace Marsoltys\QASapi;
use Marsoltys\QASapi\QASException;

class QAS
{
    /**
     * @var string default WSDL file location
     */
    private $wsdl = "";

    /**
     * @return string
     */
    public function getWsdl()
    {
        return $this->wsdl;
    }

    /**
     * @param string $wsdl
     */
    public function setWsdl($wsdl)
    {
        $this->wsdl = $wsdl;
    }

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
     * @var \SoapClient
     */
    private $client;

    /**
     * @param string $wsdl WSDL file location
     * @throws QASException
     */
    function __construct($wsdl = null) {
        if ( empty($wsdl) )
            throw new QASException("WSDL file is not set !");
        else
            $this->setWsdl($wsdl);

        $this->client = new \SoapClient($this->getWsdl());
    }

    /**
     * @param array|object $config JSON object containing QAS request configuration and action, see $defaults
     * @return Object|\SoapFault response from QAS server
     * @throws QASException
     */
    public function call ($config) {

        $action = $config['action'];

        if(empty($action))
            return "No 'action' parameter specified";
        try {
            $params = array_merge((array)$this->defaults, (array)$config['params'] );
            $this->response = $this->client->$action( $params );

            return $this->response ;

        }catch (\SoapFault $e){

            return $e;
        }
    }

    /**
     * @param string $query Search phrase
     * @return Object|\SoapFault response from QAS server
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
     * @return Object|\SoapFault response from QAS server
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
     * @return string
     * @throws QASException
     */
    public function getJson() {
        if(!empty($this->response))
            return json_encode($this->response);
        else
            throw new QASException("Please invoke one of the search methods first!");
    }
}