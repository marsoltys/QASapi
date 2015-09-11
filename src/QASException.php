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
 * Class QASException
 * @package Marsoltys\QASapi
 */
class QASException extends \Exception
{
    public function __construct($msg, $code = 0, \Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }
}