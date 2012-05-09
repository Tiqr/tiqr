<?php
/**
 * This file is part of the tiqr project.
 * 
 * The tiqr project aims to provide an open implementation for 
 * authentication using mobile devices. It was initiated by 
 * SURFnet and developed by Egeniq.
 *
 * More information: http://www.tiqr.org
 *
 * @author Ivo Jansch <ivo@egeniq.com>
 * 
 * @package tiqr
 *
 * @license New BSD License - See LICENSE file for details.
 *
 * @copyright (C) 2010-2011 SURFnet BV
 */


require_once 'PHPUnit/Framework.php';
 
require_once '../Tiqr/OATH/OCRA.php';

class ArrayTest extends PHPUnit_Framework_TestCase
{
    
    public function decimalToHex($decimalChallenge)
    {
        return dechex($decimalChallenge);
    }    

    public function testPlainChallengeResponse()
    {

        $result = OCRA::generateOCRA("OCRA-1:HOTP-SHA1-6:QN08", 
                                     "3132333435363738393031323334353637383930", 
                                     "",
                                     $this->decimalToHex("00000000"), 
                                     "", 
                                     "", 
                                     "");
                                     
        $this->assertEquals("237653", $result);
        
        $result = OCRA::generateOCRA("OCRA-1:HOTP-SHA1-6:QN08", 
                                     "3132333435363738393031323334353637383930", 
                                     "", 
                                     $this->decimalToHex("77777777"), 
                                     "", 
                                     "", 
                                     "");

        $this->assertEquals("224598", $result);        
    }

    public function testChallengeResponseWithSession()
    {        
        $result = OCRA::generateOCRA("OCRA-1:HOTP-SHA1-6:QN08-S", 
                                     "3132333435363738393031323334353637383930", 
                                     "", 
                                     $this->decimalToHex("77777777"), 
                                     "", 
                                     "ABCDEFABCDEF", 
                                     "");

        $this->assertEquals("675831", $result);        
    }
    
}
