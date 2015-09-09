<?php
/**
 * Created by PhpStorm.
 * User: soltm
 * Date: 11/08/2015
 * Time: 14:09
 *
 * Entry script for QAS API
 */

error_reporting(E_ALL & ~E_NOTICE);
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);

////  Allow crosdomain requests  ////
header("Access-Control-Allow-Origin: *");
////////////////////////////////////

/// Sanitize $_POST /////////////////////
$args = array();
$flags =[FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH];
$filter = FILTER_SANITIZE_STRING;
//function cleanRecursive($data)
//{
//    foreach ($data as $key => $value) {
//        echo $key;
//        $args[$key] = array('filter' => $filter);
//    };
//}
$_POST  = filter_input_array(INPUT_POST, $filter);

/////////////////////////////////////////

require_once('QAS.php');
require_once('Utils.php');

$qas = new QAS();
$utils = new Utils();
$result = "";

/**
 * Very basic API call example
 *
 * POST DATA:
 *      data: {
            action: 'DoSearch',
            params : {
                'Engine': {
                    '_': 'Singleline',
                    'Flatten': false,
                    'Intensity': "Close",
                    'Threshold': '5',
                    'Timeout': '30',
                },
                'Country': 'APR',
                'Layout': 'PAFWEB',
                'Refinement': '',
                'Search': 'Suffolk'
            }
        }
 *
 */

if(!empty($_POST['action'])) {
    $result = $qas->call($_POST);
}
else if(!empty($_POST['Moniker'])) {

    if($_POST['getDetails'])
        $result = $qas->getAddressDetails($_POST['Moniker']);
    else
        $result = $qas->refine($_POST['Moniker']);
}
else if(!empty($_POST)){
    $result = $qas->search($utils->formatSearchQuery($_POST));
}

if(is_a($result, 'SoapFault'))
    echo $utils->soapError($result, true);
else
    echo $qas->getJson();

