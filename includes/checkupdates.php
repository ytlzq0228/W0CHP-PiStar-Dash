<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';

if (constant("AUTO_UPDATE_CHECK") == "true") {
   echo $version; system('/usr/local/sbin/pistar-check4updates');
} else {
    echo $version;
}
?>
