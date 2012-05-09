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

$config = array(

    "identifier"          => "yourserver.uri.com",
    "name"                => "Name of your service",
    "auth.protocol"       => "protocol compiled into app",
    "enroll.protocol"     => "enroll protocol compiled in to app",

    "ocra.suite"          => "OCRA-1:HOTP-SHA1-6:QH10-S",

    "logoUrl"             => "http://path/to/your/idp/logo",
    "infoUrl"             => "http://path/to/your/info/page",

    "tiqr.path"       => "../../library/tiqr",

    "phpqrcode.path"      => "../../library/phpqrcode",

    "apns.path"           => "../../library/apns-php",
    "apns.certificate"    => "../../certificates/your_apple_push_notification_certificate.pem",
    "apns.environment"    => "sandbox",
    
    "c2dm.username"       => "username for your android c2dm account",
    "c2dm.password"       => "password",
    "c2dm.application"    => "com.example.authenticator",

    "statestorage"        => array("type" => "file"),

    "devicestorage"       => array("type"  => "tokenexchange",
                                   "url"   => "http://path/to/your/tokenexchange/server/",
                                   "appid" => "idOfYourApp"),

    "userstorage"         => array("type" => "file", "path" => "../../users", "encryption" => array('type' => 'dummy')),
    
    // "userstorage"         =>  array("type" => "ldap", 
    //                                 "encryption" => array('type' => 'dummy')
    //                                 "host" => "ldap.surfnet.nl",
    //                                 "username" => "cn=Admin,ou=Persons,ou=Office,dc=SURFnet,dc=NL",
    //                                 "password" => "*****",
    //                                 "bindRequiresDn" => true,
    //                                 "accountDomainName" => "surfnet.nl",
    //                                 "baseDn" => "dc=surfnet,dc=nl",
    //                                 "userClass" => 'organizationalPerson',
    //                                 "dnPattern" =>  "cn=%s,ou=Persons,ou=Office,dc=surfnet,dc=nl",
    //                                 "idAttr" => 'cn',
    //                                 "displayNameAttr" => 'cn',
    //                                 "notificationTypeAttr" => 'tiqrNotificationType',
    //                                 "notificationAddressAttr" => 'tiqrNotificationAddress',
    //                                 "secretAttr" => 'tiqrSecret',
    //                                 "isBlockedAttr" => 'tiqrIsBlocked',
    //                                 "loginAttemptsAttr" => 'tiqrLoginAttempts',
    //                          ),      
);
