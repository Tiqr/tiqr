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

$this->data['header'] = $this->t('{authTiqr:tiqr:header}');
$this->data['jquery'] = array('version' => '1.6', 'core' => true);

$this->includeAtTemplateBase('includes/header.php');

if (!$this->data['enroll']) {

?>

<script type="text/javascript">
function verifyLogin() {
  jQuery.get('<?php echo $this->data['verifyLoginUrl'] ?>', function(data) {
      if (data == 'NO') {
          window.setTimeout(verifyLogin, 1500);
      } else if (data.substring(0, 4) == 'URL:') {
          document.location.href = data.substring(4);
      } else {
          alert(<?php echo json_encode($this->t('{authTiqr:tiqr:verify_login_error}')); ?>);
      }
  });
}

jQuery(document).ready(verifyLogin);
</script>

<?php

}
?>
<?php 
if ($this->data['errorcode'] !== NULL) {
    include("inline_error.php");
}
?>


<h2 class="main"><?php echo $this->t('{authTiqr:tiqr:header}'); ?></h2>

<form action="?" method="post" name="f">


<p>

<?php 

if ($this->data['enroll']) {

    $intro = $this->t('{authTiqr:tiqr:intro_mustenroll_2f}', array("[user]"=>'<strong>'.htmlspecialchars($this->data['tiqrUser']['displayName']).' ('.htmlspecialchars($this->data['tiqrUser']['userId']).')</strong>'));
} else if (isset($this->data['tiqrUser'])) {
    $intro = $this->t('{authTiqr:tiqr:intro_2f}', array("[user]"=>'<strong>'.htmlspecialchars($this->data['tiqrUser']['displayName']).' ('.htmlspecialchars($this->data['tiqrUser']['userId']).')</strong>'));
    
} else {
    $intro = $this->t('{authTiqr:tiqr:intro}');
}

echo $intro;

?>

</p>

<?php if ($this->data['enroll']) { ?>
    <p><?php echo $this->t('{authTiqr:tiqr:qr_youreyesonly}');?></p>
    <p><a href="<?php echo $this->data['enrollUrl']; ?>"><?php echo $this->t('{authTiqr:tiqr:link_enroll_2f}')  ?></a>
<?php } else {
        if ($this->data['mobileDevice']) { 
?>	
    <p><?php echo $this->t('{authTiqr:tiqr:instruction_tap'.($this->data['push']?'_push':'').'}'); ?></p>
    <a href="<?php echo $this->data['authenticateUrl']; ?>">
      <img src="<?php echo $this->data['qrUrl']; ?>" />
    </a> 
    <br/> 
<?php   } else { ?>
    <p><?php echo $this->t('{authTiqr:tiqr:instruction_qr'.($this->data['push']?'_push':'').'}'); ?></p>
    <img src="<?php echo $this->data['qrUrl']; ?>" /> <br/>
<?php   } 

        // manual login for connection less phones.
        $linkStart = '<a href="#" onClick="javascript:jQuery(\'#otpform\').slideToggle();">';
?> 
    <p><?php echo $this->t('{authTiqr:tiqr:alternative_otp}',array("[link]"=>$linkStart, "[/link]"=>"</a>")); ?></p>
    <div id="otpform" <?php if (!isset($this->data['errorcode']) || $this->data['errorcode']!='wrongotp') { ?>style="display:none"<?php } ?>>
    
    <?php if (isset($this->data["attemptsLeft"]) && $this->data["attemptsLeft"]<=2) { ?>
    <span style="color: #FF0000">
    <?php 
        if ($this->data["attemptsLeft"]<=0) {
            echo $this->t('{authTiqr:tiqr:attemptsleft_zero}');
        } else { 
            echo $this->t('{authTiqr:tiqr:attemptsleft}', array("[attempts]"=>$this->data['attemptsLeft']));
        } 
            ?>
    </span>
    <?php } ?>
    
<?php if (!isset($this->data["tiqrUser"]) || !is_array($this->data["tiqrUser"])) { ?>
    <p><?php echo $this->t('{authTiqr:tiqr:label_userid}'); ?>: <input id="userid" type="text" tabindex="2" name="userId" />
<?php } ?>
    <?php echo $this->t('{authTiqr:tiqr:label_otp}'); ?>: <input id="otp" type="text" tabindex="3" name="otp" /> <button type="submit"><?php echo $this->t('{authTiqr:tiqr:go}'); ?></button></p>
    </div>
<?php } 

if (!$this->data['enroll'] && $this->data['mayCreate']) {

?>
    <p>
<?php
    $linkStart = '<a href="'.$this->data['enrollUrl'].'">'; 
    echo $this->t('{authTiqr:tiqr:instruction_no_account}', array("[link]"=>$linkStart, "[/link]"=>'</a>')); 
}
foreach ($this->data['stateparams'] as $name => $value) {
	echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
}
?>
</form>
<?php

$this->includeAtTemplateBase('includes/footer.php');
