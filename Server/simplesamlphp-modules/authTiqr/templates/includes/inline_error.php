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

?>
<div style="border: 1px solid orange; padding: 1px; margin: 14px;background: #f5f5f5">
    <img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" style="float: left; margin: 15px " />
    <?php if ($this->getTag('{authTiqr:tiqr:title_error_' . $this->data['errorcode'] . '}') != null): ?>
        <h2><?php echo $this->t('{authTiqr:tiqr:title_error_' . $this->data['errorcode'] . '}'); ?></h2>
        <p><?php echo $this->t('{authTiqr:tiqr:descr_error_' . $this->data['errorcode'] . '}'); ?></p>
    <?php  else: ?>
        <h2><?php echo $this->t('{errors:title_' . $this->data['errorcode'] . '}'); ?></h2>
        <p><?php echo $this->t('{errors:descr_' . $this->data['errorcode'] . '}'); ?></p>     
    <?php endif;?>        
</div>