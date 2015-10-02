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
 * Class Utils
 * @package Marsoltys\QASapi
 */
class Utils
{
    /**
     * Formats array of parameters (i.e. $_POST - ['street'],['postcode'], etc.)
     * to a single string ready to submit to QAS.
     * If string, it will return untouched string.
     *
     * @param $query array|string variable containing query array or string
     * @return string comma "," delimited query fields
     */
    public function formatSearchQuery($query)
    {
        if (is_array($query)) {
            $query = array_map('trim',$query);
            $query = array_filter($query);

            return join(", ", $query);
        } else {
            $query = urldecode($query);
        }

        return $query;
    }

    /**
     * Returns SoapFault exception data in HTML or JSON format
     *
     * @param \SoapFault $fault exception data
     * @param bool|false $html if exception should be HTML formatted
     * @return string
     */
    public function soapError(\SoapFault $fault, $html = false)
    {
        if ($html === true) {
            $details = str_replace(" ", "&nbsp;", json_encode($fault->detail, JSON_PRETTY_PRINT));
            $details = str_replace("\n", "<br>", $details);
            return "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring}, <br /> Details:  {$details})";
        } else {
            return json_encode($fault);
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function sanitizeInputArray($data)
    {
        $filter = FILTER_SANITIZE_STRING ;
        $flags = [FILTER_FLAG_STRIP_HIGH, FILTER_FLAG_STRIP_LOW];

        $args = $this->recursiveArgs($data, $filter, $flags);

        return filter_var_array($data, $args);
    }

    /**
     * @param array $data
     * @param int $filter
     * @param array|int $flags
     * @return arguments array for filter_var_array() function.
     */
    private function recursiveArgs($data, $filter, $flags)
    {
        $args = null;
            foreach ($data as $key => $value) {
                    $args[$key] = array('filter' => $filter, 'flags' => $flags);
            };
        return $args;
    }
}