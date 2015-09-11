<?php

/**
 *
 * Project: QAS API
 * Date: 11/09/2015
 * @author Mariusz Soltys.
 * @version 1.0.0
 * @license http://opensource.org/licenses/MIT
 *
 */
class QASTest extends PHPUnit_Framework_TestCase
{
    public function testQAS () {
        // As we can't test QAS class without WWSDL file we need to test agains exception
        //TODO Create something to be able to test SOAP calls (is it possible without actual server ?)
        try{
            new Marsoltys\QASapi\QAS();
        }catch (Exception $e){
            $this->assertEquals('Marsoltys\QASapi\QASException', get_class($e));
        }
    }
}
