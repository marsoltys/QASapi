<?php

/**
 *
 * Project: QAS API
 * Date: 01/09/2015
 * @author Mariusz Soltys.
 * @version 1.0.0
 * @license http://opensource.org/licenses/MIT
 *
 */

namespace Marsoltys\QASapi;

/**
 * Class QAS
 * @package Marsoltys\QASapi
 */
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

    /**
     * @var null
     */
    private $response = null;

    /**
     * @var \SoapClient
     */
    private $client;

    /**
     * @param string $wsdl WSDL file location
     * @throws QASException
     */
    function __construct($wsdl = null)
    {
        if ( empty($wsdl) ) {
            throw new \Marsoltys\QASapi\QASException ("WSDL file is not set !");
        }

        $this->setWsdl($wsdl);

        $this->client = new \SoapClient($this->getWsdl());
    }

    /**
     * @param array|\stdClass $config JSON object containing QAS request configuration and action, see $defaults
     * @return \stdClass|\SoapFault response from QAS server
     * @throws QASException
     */
    public function call($config)
    {
        $action = $config['action'];

        if (empty($action)) {
            throw new \Marsoltys\QASapi\QASException("No 'action' parameter specified");
        }

        try {
            $params = array_merge((array)$this->defaults, (array)$config['params'] );
            $this->response = $this->client->$action( $params );

            return $this->response ;

        } catch (\SoapFault $e) {
            return $e;
        }
    }

    /**
     * @param string $query Search phrase
     * @return \stdClass|\SoapFault response from QAS server
     */
    public function search($query)
    {
        $results = $this->call([
            'action'=>'DoSearch',
            'params'=>[
                'Search'=>$query
            ]]);

        if (is_object($results->QAPicklist->PicklistEntry) && $results->QAPicklist->PicklistEntry->CanStep) {
            return $this->refine($results->QAPicklist->PicklistEntry->Moniker);
        }

        return $results;
    }

    /**
     * Do search refinement
     *
     * @param string $moniker
     * @param string $query Search refinement
     * @return \stdClass|\SoapFault response from QAS server
     */
    public function refine ($moniker, $query = '')
    {
        $results = $this->call([
            'action'=>'DoRefine',
            'params'=>[
                'Refinement' => $query,
                'Moniker' => $moniker
            ]]);

        return $results;
    }

    /**
     * Gets full address details based on "PicklistItem.Moniker" property
     *
     * @param string $moniker
     * @param array $options
     * @return \stdClass|\SoapFault response from QAS server
     */
    public function getAddressDetails($moniker, $options = array())
    {
        $params = ['Moniker' => $moniker];
        $options = array_merge($options, $params);

        $results = $this->call([
            'action'=>'DoGetAddress',
            'params'=>$options
        ]);

        return $results;
    }

    /**
     * @return \stdClass JSON encoded array containing Available QAS methods
     */
    public function getSoapActions()
    {
        $functions = $this->client->__getFunctions();
        return $functions;
    }

    /**
     * Can be used to validate method names before doing eval()
     *
     * @return array Array containing allowed method names
     */
    public function getAllowedMethods()
    {
        $methods = ['call', 'search', 'refine', 'getAddress', 'getMethods'];
        return $methods;
    }

    /**
     * @return string JSON encoded response from server
     * @throws QASException
     */
    public function getJson()
    {
        if (empty($this->response)) {
            throw new \Marsoltys\QASapi\QASException("Please invoke one of the search methods first!");
        }

        return json_encode($this->response);
    }
}