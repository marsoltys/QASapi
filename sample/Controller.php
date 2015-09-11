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

require_once __DIR__ . '/../vendor/autoload.php';

use Marsoltys\QASapi\QAS;
use Marsoltys\QASapi\Utils;

/**
 * @var $wsdl string URL to QAS WSDL file, i.e. "http://somehost.com/proweb.wsdl"
 */
$wsdl = "";

$qas = new QAS($wsdl);
$utils = new Utils();
$result = "";


/**
 * Very basic API call example
 */


/**
 * $qas->call() Invokes method provided in 'action' element of array
 *
 * $_POST = array(
 *      'action'=>'DoGetData'
 *      'params'=> [
 *          ...
 *      ]
 * )
 *
 */

if(!empty($_POST['action'])) {
    $result = $qas->call($_POST);
}

/// Lets step-in, refine results or get full adress details- invokes DoRefine action
else if(!empty($_POST['Moniker'])) {

    if(!empty($_POST['getDetails']))
        // get full address details
        $result = $qas->getAddressDetails($_POST['Moniker']);
    else
        // step-in / refine results
        $result = $qas->refine($_POST['Moniker']);
}

/// Lets do initial request - invokes DoSearch action
else if(!empty($_POST)){
    $test = $utils->sanitizeInputArray($_POST);
    $result = $qas->search($utils->formatSearchQuery($test));
}

/**
 * If SoapFault was returned from server
 */
if(is_a($result, 'SoapFault'))
    echo $utils->soapError($result, true);
else
    // If everything is ok, return JSON string
    echo $qas->getJson();

