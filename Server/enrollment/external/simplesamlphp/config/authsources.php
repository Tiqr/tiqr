<?php
$config = array(
    // This is the name of this authentication source, and will be used to access it later.
    'default-sp' => array(
        'saml:SP',
        'idp' => 'http://login.surfnet.nl/simplesaml/saml2/idp/metadata.php',
        'authproc' => array(
           50 => array(
               'class' => 'core:AttributeMap',
               'oid2name',
           ),
       ),
    )
);
