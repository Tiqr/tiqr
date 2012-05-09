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

if (!array_key_exists('AuthState', $_REQUEST)) {
	throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
}
$authState = $_REQUEST['AuthState'];
$state = SimpleSAML_Auth_State::loadState($authState, sspmod_authTiqr_Auth_Tiqr::STAGEID);

if (array_key_exists('otp', $_REQUEST)) {
	$otp = $_REQUEST['otp'];
} else {
	$otp = '';
}

if (array_key_exists('userId', $_REQUEST)) {
    $userId = $_REQUEST['userId'];
} else if (isset($state["tiqrUser"])) {
    $userId = $state["tiqrUser"]["userId"]; // two factor
    
} else {
    $userId = '';
}

$attemptsLeft = NULL;

if (!empty($otp)) {
	/*  attempt to log in. */
	$result = sspmod_authTiqr_Auth_Tiqr::processManualLogin($userId, $otp, $state[sspmod_authTiqr_Auth_Tiqr::SESSIONKEYID]);

	if ($result=="OK") {
	     $url = SimpleSAML_Module::getModuleURL('authTiqr/complete.php');
         SimpleSAML_Utilities::redirect($url, array('AuthState' => $authState));
         exit;
	} else {
	    $errorCode = "wrongotp";
	    if (strpos($result, ":")!==false) {
	        $elems = split(":", $result);
            $attemptsLeft = $elems[1];
	    }
	}
	
} else {
	$errorCode = NULL;
	
	// Initialize a new session. 
	$state[sspmod_authTiqr_Auth_Tiqr::SESSIONKEYID] = sspmod_authTiqr_Auth_Tiqr::startAuthenticationSession($userId, $state);
    SimpleSAML_Auth_State::saveState($state, sspmod_authTiqr_Auth_Tiqr::STAGEID);
}



$push = false; 
$enroll = false; 
$mayCreate = true;
$enrollUrl = "";

$globalConfig = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($globalConfig, 'authTiqr:login.php');

$t->data['stateparams'] = array('AuthState' => $authState);

if (isset($state["tiqrUser"])) {
    $mayCreate = false;
    
    // 2 factor authentication
    if (!sspmod_authTiqr_Auth_Tiqr::isEnrolled($state["tiqrUser"]["userId"])) { 
        $enrollUrl = SimpleSAML_Module::getModuleURL('authTiqr/newuser.php');
        $enrollUrl.='?'.http_build_query(array('AuthState' => $authState));    
        $enroll = true;
    } else {    
        // we have a user. Send notification.
        $push = sspmod_authTiqr_Auth_Tiqr::sendAuthNotification($authState);
    }
} else {
    $enrollUrl = SimpleSAML_Module::getModuleURL('authTiqr/newuser.php').'?'.http_build_query($t->data['stateparams']);
}

$t->data['errorcode'] = $errorCode;
if ($attemptsLeft!=NULL) {
    $t->data['attemptsLeft'] = $attemptsLeft;
}
$t->data['push'] = $push;
$t->data['enroll'] = $enroll;
$t->data['enrollUrl'] = $enrollUrl;
$t->data['mayCreate'] = $mayCreate;

$t->data['verifyLoginUrl'] = SimpleSAML_Module::getModuleURL('authTiqr/verify.php').'?'.http_build_query($t->data['stateparams']);
$t->data['mobileDevice'] = (preg_match('/iphone/i', $_SERVER["HTTP_USER_AGENT"]) || preg_match('/android/i', $_SERVER["HTTP_USER_AGENT"]));

if ($t->data['mobileDevice']) {
    $returnUrl = SimpleSAML_Module::getModuleURL('authTiqr/complete.php').'?'.http_build_query($t->data['stateparams']);
    $t->data['authenticateUrl'] = sspmod_authTiqr_Auth_Tiqr::getAuthenticateUrl($state[sspmod_authTiqr_Auth_Tiqr::SESSIONKEYID]).'?'.urlencode($returnUrl);
}

$t->data['qrUrl'] = SimpleSAML_Module::getModuleURL('authTiqr/qr.php').'?'.http_build_query($t->data['stateparams']);


if (isset($state["tiqrUser"])) {
    $t->data['tiqrUser'] = $state["tiqrUser"];
}

$t->show();
exit();


