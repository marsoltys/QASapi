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


class QASExceptionTest extends PHPUnit_Framework_TestCase
{
    function testException()
    {
        try {
            new Marsoltys\QASapi\QAS();
        } catch (Exception $e) {
            $this->assertEquals('Marsoltys\QASapi\QASException', get_class($e));
        }
    }
}
