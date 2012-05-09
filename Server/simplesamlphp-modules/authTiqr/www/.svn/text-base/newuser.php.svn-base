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

$state = NULL;

$userId = NULL;
$displayName = NULL;
$errorcode = NULL;
$mayCreate = true;

$session = SimpleSAML_Session::getInstance();

if (isset($_REQUEST['AuthState'])) {
    $authState = $_REQUEST['AuthState'];
    $state = SimpleSAML_Auth_State::loadState($authState, sspmod_authTiqr_Auth_Tiqr::STAGEID);    
    
    if (is_array($state)) {
        $config = sspmod_authTiqr_Auth_Tiqr::getAuthSourceConfig($authState);
        
        if (isset($config["enroll.authsource"])) {
            
            $mayCreate = false;
            
            if ($session->isValid($config["enroll.authsource"])) {
                $attributes = $session->getAttributes();
                // Check if userid exists
                $uidAttribute = $config["enroll.uidAttribute"];
                $displayNameAttribute = $config["enroll.cnAttribute"];
                if (!isset($attributes[$uidAttribute]))
                    throw new Exception('User ID is missing');
                $state["tickrUser"]["userId"] = $attributes[$uidAttribute][0];
                $state["tickrUser"]["displayName"] = $attributes[$displayNameAttribute][0];
            } else {
                SimpleSAML_Auth_Default::initLogin(
                         $config["enroll.authsource"],
                         SimpleSAML_Utilities::selfURL(),
                         NULL,
                         $_REQUEST);
            }
               
        }
        
    }
}

$template = 'newuser.php';

$store = sspmod_authTiqr_Auth_Tiqr::getUserStorage();

if (is_array($_POST) && count($_POST) && isset($_POST["create"])) {
    
    // Page was posted, so new user form has been filled.
    if ($state==NULL) {
  //      throw new SimpleSAML_Error_NoState();
    }
    
    $displayName = isset($_POST['displayName'])?$_POST['displayName']:NULL;
    $userId = isset($_POST['userId'])?$_POST['userId']:NULL;
    
    if (empty($userId) || empty($displayName)) {
        
        $errorcode = "userdatarequired";

    } else if (!preg_match('/^[A-Za-z0-9_\.]*$/',$userId)) {

        $errorcode = "invaliduserid"; 

    } else {

        if ($store->userExists($userId)) {
            // User already exists. If we don't have a secret yet, we must however still enroll him.
            if (!$store->getSecret($userId)) {
                $template = "newuser_result.php";
            } else {
                $errorcode = 'userexists';
            }
        } else if (!$store->createUser($userId, $displayName)) {
            $errorcode = 'createfailed';
        } else {
            // User created, show enrollment QR
            $template = "newuser_result.php";
        }
  
    }
    
} else if (is_array($state) && isset($state["tiqrUser"])) {

    $userId = $state["tiqrUser"]["userId"];
    $displayName = $state["tiqrUser"]["displayName"];
    
    // pre-authenticated user, so must be in second factor
    if ($store->userExists($userId)) {
        // User already exists. If we don't have a secret yet, we must however still enroll him.
        if (!$store->getSecret($userId)) {
            $template = "newuser_result.php";
        } else {
            $errorcode = 'userexists';
        }
    } else if (!$store->createUser($userId, $displayName)) {
        $errorcode = 'createfailed';
    } else {
        // User created, show enrollment QR
        $template = "newuser_result.php";
    }
    
    $mayCreate = false;

} else {
    
    // Display new user dialogue, if allowed. 
}
            
$globalConfig = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($globalConfig, 'authTiqr:'.$template);
if ($authState!=NULL) {
    $t->data['stateparams'] = array('AuthState' => $authState);
}
$t->data['errorcode'] = $errorcode;
$t->data['userId'] = $userId;
$t->data['displayName'] = $displayName;
$t->data['mayCreate'] = $mayCreate;

if ($template=="newuser_result.php") {
    // Store data for QR code if we're showing an enrollment QR
       
    $session->setData("String", "enroll_userid", $userId, 10);
    $session->setData("String", "enroll_fullname", $displayName, 10);
    
    $t->data['qrUrl'] = SimpleSAML_Module::getModuleURL('authTiqr/qr_enroll.php');
    $t->data['verifyEnrollUrl'] = SimpleSAML_Module::getModuleURL('authTiqr/verify_enrollment.php').'?'.http_build_query($t->data['stateparams']);
    
    if ($authState!=NULL) {
        $t->data['loginUrl'] = SimpleSAML_Module::getModuleURL('authTiqr/login.php').'?'.http_build_query($t->data['stateparams']);
    }
}


$t->show();
exit();

