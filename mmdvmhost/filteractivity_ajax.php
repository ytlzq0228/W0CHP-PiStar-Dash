<?php

if($_POST['action'] == 'true') {
    exec('sudo mount -o remount,rw /');
    exec('sudo touch /etc/.FILTERACTIVITY');
    exec('sudo mount -o remount,ro /');
}

if($_POST['action'] == 'false') {
    exec('sudo mount -o remount,rw /');
    exec('sudo rm -rf /etc/.FILTERACTIVITY');
    exec('sudo mount -o remount,ro /');
}

?>
