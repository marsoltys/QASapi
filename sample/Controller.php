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

require_once __DIR__ . '/../vendor/autoload.php';

use Marsoltys\QASapi\QAS;
use Marsoltys\QASapi\Utils;

/**
 * @var $wsdl string URL to QAS WSDL file, i.e. "http://somehost.com/proweb.wsdl"
 */
$wsdl = "http://vsvr-dev897.euser.eroot.eadidom.com:2026/proweb.wsdl";

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

if (!empty($_POST['action'])) {
    $result = $qas->call($_POST);
} elseif (!empty($_POST['Moniker'])) {
    // Lets step-in, refine results or get full adress details- invokes DoRefine action
    if (!empty($_POST['getDetails'])) {
        // get full address details
        $result = $qas->getAddressDetails($_POST['Moniker']);
    } else {
        // step-in / refine results
        $result = $qas->refine($_POST['Moniker']);
    }
} elseif (!empty($_POST)) {
    // Lets do initial request - invokes DoSearch action
    $test = $utils->sanitizeInputArray($_POST);
    $result = $qas->search($utils->formatSearchQuery($test));
}

if (is_a($result, 'SoapFault')) {
    //If SoapFault was returned from server
    echo $utils->soapError($result, true);
} else {
    // If everything is ok, return JSON string
    echo $qas->getJson();
}

