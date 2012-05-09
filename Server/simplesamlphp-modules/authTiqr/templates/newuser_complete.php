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

$this->data['header'] = $this->t('{authTiqr:tiqr:header_enrollment}');

$this->includeAtTemplateBase('includes/header.php');

?>

<?php
if ($this->data['errorcode'] !== NULL) {
    include("inline_error.php");
}
?>

<h2 class="main"><?php echo $this->t('{authTiqr:tiqr:header_enrollment}'); ?></h2>

<p>
<?php 

if (isset($this->data['loginUrl'])) { 
    $linkStart = '<a href="'.$this->data['loginUrl'].'">';

    echo $this->t('{authTiqr:tiqr:enrollment_success}', array('[link]'=>$linkStart, '[/link]'=>'</a>')); 
} else {
    $linkStart = '<a href="javascript:window.close();">';
    echo $this->t('{authTiqr:tiqr:close_this_window}', array('[link]'=>$linkStart, '[/link]'=>'</a>'));
}
?>
</p> 
<?php

$this->includeAtTemplateBase('includes/footer.php');
