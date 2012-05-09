<?php

include_once("include.php");

$content = renderTemplate("../templates/intro.phtml");
echo renderTemplate("../templates/piggybank.phtml", array("content"=>$content));
