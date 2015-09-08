<?php

/**
 * Created by PhpStorm.
 * User: Mariusz Soltys
 * Date: 08/09/2015
 * Time: 11:57
 */
class Utils
{
    /**
     * Formats array of parameters (i.e. $_POST - ['street'],['postcode'], etc.)
     * to single string ready to submit to QAS.
     * If string, it will return untouched string.
     *
     * @param $query array|string variable containing query array or string
     * @return string comma "," delimited query fields
     */
    public function formatSearchQuery($query) {
        if(is_array($query)) {
            $query = array_filter($query);
            return implode(", ", $query);
        }

        return $query;
    }

    public function soapError(SoapFault $fault, $html = false){
        if($html === true){
            $details = str_replace(" ", "&nbsp;", json_encode($fault->detail, JSON_PRETTY_PRINT));
            $details = str_replace("\n", "<br>", $details);
            return "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring}, <br /> Details:  {$details})";
        }else{
            return json_encode($fault);
        }
    }

    public function sanitizeInput($type)
    {
        $args = array();
        $flags = [FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH];
        $filter = FILTER_SANITIZE_STRING;

        foreach ($_POST as $key => $value) {
            $args[$key] = array('filter' => $filter, 'flags' => $flags);
        };

        return filter_input_array($type, $args);
    }
}