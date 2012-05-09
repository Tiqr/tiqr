<?php

include_once("include.php");

$as = new SimpleSAML_Auth_Simple('authTiqr_v4');

$as->requireAuth();

$logoutUrl = $as->getLogoutURL();

$attributes = $as->getAttributes();
$name = "";
if (isset($attributes["urn:mace:dir:attribute-def:uid"])) {
    $uid = $attributes["urn:mace:dir:attribute-def:uid"][0];
    $name = $attributes["urn:mace:dir:attribute-def:displayName"][0];
} else {
    $uid = $attributes["uid"][0];
}
if ($name=="") {
    $name = $uid;
} else {
    $name = $name." (".$uid.")";
}
$content = renderTemplate("../templates/loggedin.phtml", array("uid"=>$name, "logoutUrl"=>$logoutUrl));

echo renderTemplate("../templates/piggybank.phtml", array("content"=>$content, "logoutUrl"=>$logoutUrl));

