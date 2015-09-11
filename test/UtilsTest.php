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


class UtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Marsoltys\QASapi\Utils
     */
    private $utils;

    protected function setUp()
    {
        parent::setUp();
        $this->utils = new \Marsoltys\QASapi\Utils();
    }

    public function testFormatSearchQuery()
    {
        $_POST = [
            'Flat' => 4,
            'House Name' => 'Some House',
            'Street' => 'Some Street  ',
            'Town' => 'Some Town',
            'Postcode' => 'III PPP  ',
            'County' => 'Some County'
        ];

        $this->assertEquals("4, Some House, Some Street, Some Town, III PPP, Some County", $this->utils->formatSearchQuery($_POST));
        $this->assertEquals("some address", $this->utils->formatSearchQuery("some address"));
    }

    public function testSanitizeInput()
    {
        $_POST = array(
            'product_id'    => 'libgd <?php ?>! *& ()/ \ ^ % £',
            'component'     => '10',
            'versions'      => '2.0.33',
            'testscalar'    => array('2', '23', '10', '12'),
            'testarray'     => '2',
        );

        $expected = array(
            'product_id'    => 'libgd ! *& ()/ \ ^ % £',
            'component'     => '10',
            'versions'      => '2.0.33',
            'testscalar'    => false,
            'testarray'     => '2',
        );

        $this->assertEquals($expected, $this->utils->sanitizeInputArray($_POST));
    }


}