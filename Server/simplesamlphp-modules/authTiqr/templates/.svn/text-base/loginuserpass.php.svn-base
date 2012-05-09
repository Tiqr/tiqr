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

if (strlen($this->data['username']) > 0) {
	$this->data['autofocus'] = 'password';
} else {
	$this->data['autofocus'] = 'username';
}

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

<p>
    <?php  echo $this->t('{authTiqr:tiqr:intro}'); ?>
</p>

<?php if ($this->data['mobileDevice']): ?> 
    <p>
        <?php echo $this->t('{authTiqr:tiqr:instruction_tap' . ($this->data['push'] ? '_push' : '') . '}'); ?>
    </p>
    <p>
    	<a href="<?php echo htmlentities($this->data['authenticateUrl']) ?>">
    		<img src="<?php echo htmlentities($this->data['qrUrl']) ?>" />
    	</a> 
    </p> 
<?php else: ?>
    <p>
        <?php echo $this->t('{authTiqr:tiqr:instruction_qr' . ($this->data['push'] ? '_push' : '') . '}'); ?><br/>
    	<img src="<?php echo htmlentities($this->data['qrUrl']) ?>" />
    </p>
<?php endif; ?> 

<div id="otpform" <?php if ($this->data['type'] != 'otp'): ?>style="display:none"<?php endif; ?>>
	<form action="?" method="post" name="f">
	<input type="hidden" name="type" value="otp" />

    <?php if (isset($this->data["attemptsLeft"]) && $this->data["attemptsLeft"] <= 2): ?>
    	<span style="color: #FF0000">
        <?php 
            if ($this->data["attemptsLeft"] <= 0) {
                echo $this->t('{authTiqr:tiqr:attemptsleft_zero}');
            } else { 
                echo $this->t('{authTiqr:tiqr:attemptsleft}', array("[attempts]" => $this->data['attemptsLeft']));
            } 
        ?>
    	</span>
    <?php endif; ?>
    
    <table>
    	<tr>
    		<td style="padding: .3em;"><?php echo $this->t('{login:username}'); ?></td>
    		<td>
    			<input type="text" id="username" tabindex="1" name="username" value="<?php echo htmlspecialchars($this->data['username']); ?>" />
    		</td>
    		<td style="padding: .4em;" rowspan="3">
    			<input type="submit" tabindex="4" value="<?php echo $this->t('{login:login_button}'); ?>" />
    		</td>
    	</tr>
    	<tr>
    		<td style="padding: .3em;"><?php echo $this->t('{authTiqr:tiqr:label_otp}'); ?></td>
    		<td><input id="password" type="text" tabindex="2" name="otp" /></td>
    	</tr>
    </table>   
    
    <?php
    foreach ($this->data['stateparams'] as $name => $value) {
	    echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
    }
    ?>   
    
    </form>    
    
    <?php
        // user/pass login
        $linkStart = '<a href="#" onClick="javascript:jQuery(\'#otpform\').slideToggle(); jQuery(\'#loginform\').slideToggle();">';
    ?> 
    <p>
        <?php echo $this->t('{authTiqr:tiqr:alternative_userpass}', array("[link]" => $linkStart, "[/link]" => "</a>")); ?>
    </p>
</div>

<div id="loginform" <?php if ($this->data['type'] != 'userpass'): ?>style="display:none"<?php endif; ?>>
	<form action="?" method="post" name="f">
	<input type="hidden" name="type" value="userpass" />	

    <p>
        <?php  echo $this->t('{authTiqr:tiqr:instruction_userpass}'); ?>
    </p>
    
    <table>
    	<tr>
    		<td rowspan="3"><img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-authentication.48x48.png" alt="" /></td>
    		<td style="padding: .3em;"><?php echo $this->t('{login:username}'); ?></td>
    		<td>
    			<input type="text" id="username" tabindex="1" name="username" value="<?php echo htmlspecialchars($this->data['username']); ?>" />
    		</td>
    		<td style="padding: .4em;" rowspan="3">
    			<input type="submit" tabindex="4" value="<?php echo $this->t('{login:login_button}'); ?>" />
    		</td>
    	</tr>
    	<tr>
    		<td style="padding: .3em;"><?php echo $this->t('{login:password}'); ?></td>
    		<td><input id="password" type="password" tabindex="2" name="password" /></td>
    	</tr>
    </table>
    
    <?php
    foreach ($this->data['stateparams'] as $name => $value) {
    	echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
    }
    ?>
    
    </form>    
    
    <?php
        // manual login for connection less phones.
        $linkStart = '<a href="#" onClick="javascript:jQuery(\'#otpform\').slideToggle(); jQuery(\'#loginform\').slideToggle(); document.getElementById(\'otp\').value = \'\';">';
    
        if (!$this->data['mobileDevice']): 
    ?> 
    <p>
        <?php echo $this->t('{authTiqr:tiqr:alternative_otp}', array("[link]" => $linkStart, "[/link]" => "</a>")); ?>
    </p>
    <?php
        endif; 
    ?>
</div>

<?php

if(!empty($this->data['links'])) {
	echo '<ul class="links" style="margin-top: 2em">';
	foreach($this->data['links'] AS $l) {
		echo '<li><a href="' . htmlspecialchars($l['href']) . '">' . htmlspecialchars($this->t($l['text'])) . '</a></li>';
	}
	echo '</ul>';
}


$this->includeAtTemplateBase('includes/footer.php');
?>