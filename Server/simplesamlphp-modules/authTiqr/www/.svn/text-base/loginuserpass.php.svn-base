<?php
/**
 * This file is part of the Sufnet SimpleSAMLphp module.
 * 
 * @author Peter Verhage <peter@egeniq.com>
 * 
 * @package simpleSAMLphp
 * @subpackage 
 *
 * @license New BSD License - See LICENSE file in the tiqr library for details
 * @copyright (C) 2010-2011 SURFnet BV
 *
 */

if (!array_key_exists('AuthState', $_REQUEST)) {
	throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
}

// Retrieve the authentication state.
$authStateId = $_REQUEST['AuthState'];
$state = SimpleSAML_Auth_State::loadState($authStateId, sspmod_authTiqr_Auth_Tiqr::STAGEID);

$type = 'userpass';
if (array_key_exists('type', $_REQUEST) && $_REQUEST['type'] == 'otp') {
    $type = 'otp';
}

if (array_key_exists('username', $_REQUEST)) {
	$username = $_REQUEST['username'];
} elseif (isset($state['core:username'])) {
	$username = (string)$state['core:username'];
} else {
	$username = '';
}

if (array_key_exists('otp', $_REQUEST)) {
	$otp = $_REQUEST['otp'];
} else {
	$otp = '';
}

if (array_key_exists('password', $_REQUEST)) {
	$password = $_REQUEST['password'];
} else {
	$password = '';
}

$attemptsLeft = NULL;

if ($type == 'otp' && !empty($username) && !empty($otp)) {
	// attempt tiqr otp login
	$result = sspmod_authTiqr_Auth_Tiqr::processManualLogin($userId, $otp, $state[sspmod_authTiqr_Auth_Tiqr::SESSIONKEYID]);

	if ($result == "OK") {
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
} elseif ($type == 'userpass' && !empty($username) && !empty($password)) {
    // attempt user-password login
	$errorCode = sspmod_authTiqr_Auth_Source_TiqrUserPass::handleUserPassLogin($authStateId, $username, $password);
} else {
	$errorCode = NULL;
	
	// Initialize a new Tiqr session. 
	$state[sspmod_authTiqr_Auth_Tiqr::SESSIONKEYID] = sspmod_authTiqr_Auth_Tiqr::startAuthenticationSession($userId, $state);
    SimpleSAML_Auth_State::saveState($state, sspmod_authTiqr_Auth_Tiqr::STAGEID);	
}

$globalConfig = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($globalConfig, 'authTiqr:loginuserpass.php');
$t->data['type'] = $type;
$t->data['stateparams'] = array('AuthState' => $authStateId);
$t->data['errorcode'] = $errorCode;

$t->data['username'] = $username;

if ($attemptsLeft != NULL) {
    $t->data['attemptsLeft'] = $attemptsLeft;
}

$t->data['verifyLoginUrl'] = SimpleSAML_Module::getModuleURL('authTiqr/verify.php').'?'.http_build_query($t->data['stateparams']);
$t->data['mobileDevice'] = (preg_match('/iphone/i', $_SERVER["HTTP_USER_AGENT"]) || preg_match('/android/i', $_SERVER["HTTP_USER_AGENT"]));

if ($t->data['mobileDevice']) {
    $returnUrl = SimpleSAML_Module::getModuleURL('authTiqr/complete.php').'?'.http_build_query($t->data['stateparams']);
    $t->data['authenticateUrl'] = sspmod_authTiqr_Auth_Tiqr::getAuthenticateUrl($state[sspmod_authTiqr_Auth_Tiqr::SESSIONKEYID]).'?'.urlencode($returnUrl);
}

$t->data['qrUrl'] = SimpleSAML_Module::getModuleURL('authTiqr/qr.php').'?'.http_build_query($t->data['stateparams']);

if (isset($state['SPMetadata'])) {
	$t->data['SPMetadata'] = $state['SPMetadata'];
} else {
	$t->data['SPMetadata'] = NULL;
}

$t->show();
exit();