<?php

$verify_login_error = json_encode($this->t('{authTiqr:tiqr:verify_login_error}'));

$this->data['htmlinject']['htmlContentPost'][] = <<<JAVASCRIPT
<script type="text/javascript">
function verifyLogin() {
  jQuery.get('{$this->data['verifyLoginUrl']}', function(data) {
      if (data == 'NO') {
          window.setTimeout(verifyLogin, 1500);
      } else if (data.substring(0, 4) == 'URL:') {
          document.location.href = data.substring(4);
      } else {
          alert("{$this->t('{authTiqr:tiqr:verify_login_error}')}");
      }
  });
}
jQuery(document).ready(verifyLogin);
</script>
JAVASCRIPT;

?>
