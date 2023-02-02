<?php

if($_POST['action'] == 'true') {
    exec('sudo mount -o remount,rw /');
    exec('sudo touch /etc/.FILTERACTIVITY');
}

if($_POST['action'] == 'false') {
    exec('sudo mount -o remount,rw /');
    exec('sudo rm -rf /etc/.FILTERACTIVITY');
}

?>
