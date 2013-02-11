<?php
/**
 * This file is part of simpleSAMLphp.
 * 
 * The authTiqr module is a module adding authentication via the tiqr 
 * project to simpleSAMLphp. It was initiated by SURFnet and 
 * developed by Egeniq.
 *
 * See the README file for instructions and requirements.
 *
 * @author Ivo Jansch <ivo@egeniq.com>
 * 
 * @package simpleSAMLphp
 * @subpackage authTiqr
 *
 * @license New BSD License - See LICENSE file in the tiqr library for details
 * @copyright (C) 2010-2011 SURFnet BV
 *
 */

$result = sspmod_authTiqr_Auth_Tiqr::processMobileLogin($_REQUEST);

if (is_array($result)) {
    header('Content-type: application/json');
    header('X-TIQR-Protocol-Version:'.sspmod_authTiqr_Auth_Tiqr::getProtocolVersion(true));
    echo json_encode($result);
} else {
    // V1 ascii protocol (didn't use an X-TIQR header yet)
    echo $result;
}
