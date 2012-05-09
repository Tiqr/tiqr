<?php

include_once("include.php");

$as = new SimpleSAML_Auth_Simple('authTiqr');

$as->requireAuth();

$logoutUrl = $as->getLogoutURL();

$attributes = $as->getAttributes();
$uid = $attributes["uid"][0];

$displayName = $attributes["displayName"][0];

$content = renderTemplate("../templates/loggedin.phtml", array("uid"=>$uid, "logoutUrl"=>$logoutUrl, "displayName"=>$displayName));

echo renderTemplate("../templates/piggybank.phtml", array("content"=>$content, "logoutUrl"=>$logoutUrl));

